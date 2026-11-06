<?php

namespace App\Services;

use App\Helpers\CurrencyHelper;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Package;
use App\Models\User;
use App\Notifications\CustomerBookingNotification;
use App\Notifications\NewBookingNotification;
use App\Repositories\BookingRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class BookingService
{
    public function __construct(
        private BookingRepository $repository
    ) {}

    /**
     * Apakah paket/tanggal ini masih boleh dipesan.
     *
     * Dulu selalu mengembalikan true, sehingga pemesanan untuk tanggal yang
     * SUDAH LEWAT tetap diterima dan masuk ke daftar pesanan sebagai kerja
     * admin yang harus dibatalkan manual. Lead time-nya sendiri sudah ditegakkan
     * di StoreBookingRequest, tapi create() juga dipanggil dari admin dan dari
     * jalur lain yang tidak melewati form request itu — jadi penjaga terakhirnya
     * harus ada di sini.
     */
    public function isAvailable(array $data): bool
    {
        if (empty($data['startDate'])) {
            return false;
        }

        try {
            $start = \Carbon\Carbon::parse($data['startDate'])->startOfDay();
        } catch (\Throwable $e) {
            return false;
        }

        if ($start->lt(now()->startOfDay())) {
            return false;
        }

        // Tanggal yang diblokir admin, bila diatur.
        $blackouts = optional(\App\Models\Setting::where('key', 'booking_settings')->first())->value['blackout_dates'] ?? [];
        foreach ((array) $blackouts as $blocked) {
            try {
                if (\Carbon\Carbon::parse($blocked)->isSameDay($start)) {
                    return false;
                }
            } catch (\Throwable $e) {
                // Baris yang salah format diabaikan, jangan sampai menjatuhkan pemesanan.
                continue;
            }
        }

        return true;
    }

    public function create(array $data)
    {
        try {
            $booking = DB::transaction(function () use ($data) {
                if (! $this->isAvailable($data)) {
                    throw new \Exception('Armada atau Paket tidak tersedia pada tanggal yang dipilih.');
                }

                // Generate booking code
                $data['bookingCode'] = $this->generateBookingCode();

                // Calculate total price & cost
                $prices = $this->calculateTotalPriceAndCost($data);
                $data['totalPrice'] = $prices['price'];
                $data['total_cost'] = $prices['cost'];

                // Freeze the currency of this booking. totalPrice follows the
                // package price list (ringgit); totalPrice_idr is stamped once,
                // here, and is what every report and tax figure reads later. It
                // must never be re-derived, or an admin editing the exchange
                // rate would retroactively change issued invoices and past
                // months' revenue.
                $data['currency'] = CurrencyHelper::PRICE_BASE;
                $data['exchange_rate_idr'] = CurrencyHelper::getRate('IDR');
                $data['totalPrice_idr'] = CurrencyHelper::toIdr($prices['price']);

                // Set default status
                $data['status'] = $data['status'] ?? 'pending';

                // Sync Customer — include soft-deleted records so a repeat booking
                // from a previously removed customer restores that record instead
                // of hitting the unique index with a fresh insert.
                //
                // Dikunci pada NOMOR TELEPON, bukan email: email tidak lagi
                // diminta saat memesan. Kalau tetap dikunci pada email, setiap
                // tamu tanpa email akan bertabrakan jadi satu baris pelanggan
                // yang sama — namanya bertukar-tukar setiap ada pesanan baru.
                $phoneKey = Customer::phoneKey($data['customerPhone'] ?? null);
                $email = trim((string) ($data['customerEmail'] ?? '')) ?: null;
                $data['customerEmail'] = $email;

                if ($phoneKey !== null) {
                    $customer = Customer::withTrashed()->firstOrNew(['phone_key' => $phoneKey]);
                } elseif ($email !== null) {
                    $customer = Customer::withTrashed()->firstOrNew(['email' => $email]);
                } else {
                    // Tanpa keduanya tidak ada yang bisa dijadikan pengenal;
                    // buat baris baru daripada menempel ke pelanggan asing.
                    $customer = new Customer;
                }

                $customer->fill([
                    'name' => $data['customerName'],
                    'phone' => $data['customerPhone'] ?? null,
                    'phone_key' => $phoneKey,
                ]);
                // Email hanya ditimpa bila memang diisi — pesanan baru tanpa
                // email tidak boleh menghapus alamat yang sudah tercatat.
                if ($email !== null) {
                    $customer->email = $email;
                }
                if ($customer->trashed()) {
                    $customer->deleted_at = null;
                }
                $customer->save();
                $data['customerId'] = $customer->id;

                $createdBooking = $this->repository->create($data);

                // Update customer stats
                $customer->update([
                    'total_bookings' => $customer->bookings()->count(),
                    // Sum the frozen IDR figures, not totalPrice: bookings made
                    // before the ringgit price list are stored in rupiah, so
                    // adding raw totalPrice across both would be meaningless.
                    'total_spent' => $customer->bookings()->whereIn('status', ['confirmed', 'completed'])->sum('totalPrice_idr'),
                    'last_booking_at' => now(),
                ]);

                return $createdBooking;
            });

            // Notify Admins — must match the actual role names used by the role
            // middleware (superadmin / admin_tour / admin_umum). The old list
            // ('admin', 'superadmin') silently skipped admin_tour & admin_umum.
            try {
                $admins = User::whereIn('role', ['superadmin', 'admin_tour', 'admin_umum'])->get();
                Notification::send($admins, new NewBookingNotification($booking));
            } catch (\Exception $ne) {
                Log::warning('Failed to send admin booking notification: '.$ne->getMessage());
            }

            // Notify Customer with Invoice
            try {
                $booking->notify(new CustomerBookingNotification($booking));
            } catch (\Exception $ce) {
                Log::warning('Failed to send customer booking notification: '.$ce->getMessage());
            }

            Log::info('New booking created: '.$booking->bookingCode, ['booking_id' => $booking->id]);

            return $booking;
        } catch (\Exception $e) {
            Log::error('Booking Creation Failed: '.$e->getMessage());
            throw $e;
        }
    }

    public function update(Booking $booking, array $data)
    {
        return DB::transaction(function () use ($booking, $data) {
            // Admin mengoreksi totalPrice (ringgit). totalPrice_idr — yang dibaca
            // laporan pendapatan & total_spent pelanggan — harus ikut diperbarui,
            // TAPI dengan kurs BEKU booking ini (exchange_rate_idr), bukan kurs
            // terkini, agar invoice & pendapatan bulan lalu tidak berubah surut.
            if (array_key_exists('totalPrice', $data)) {
                $rate = (float) ($booking->exchange_rate_idr ?? 0);
                if (strtoupper((string) $booking->currency) === 'IDR') {
                    // Pesanan lama pra-ringgit: totalPrice sudah dalam rupiah.
                    $data['totalPrice_idr'] = (float) $data['totalPrice'];
                } elseif ($rate > 0) {
                    $data['totalPrice_idr'] = round((float) $data['totalPrice'] * $rate, 2);
                } else {
                    // Data lama tanpa kurs beku: jaring pengaman pakai kurs terkini.
                    $data['totalPrice_idr'] = CurrencyHelper::toIdr((float) $data['totalPrice']);
                }
            }

            $updated = $this->repository->update($booking, $data);

            // Harga/status berubah → segarkan statistik pelanggan agar total_spent
            // tetap cocok dengan jumlah totalPrice_idr pesanan confirmed/completed.
            $customer = $updated->customer;
            if ($customer) {
                $customer->update([
                    'total_bookings' => $customer->bookings()->count(),
                    'total_spent' => $customer->bookings()
                        ->whereIn('status', ['confirmed', 'completed'])
                        ->sum('totalPrice_idr'),
                ]);
            }

            return $updated;
        });
    }

    public function updateStatus(Booking $booking, string $status)
    {
        return $this->repository->update($booking, ['status' => $status]);
    }

    public function delete(Booking $booking)
    {
        return $this->repository->delete($booking);
    }

    public function bulkDelete(array $ids)
    {
        return DB::transaction(function () use ($ids) {
            return Booking::whereIn('id', $ids)->delete();
        });
    }

    private function generateBookingCode(): string
    {
        do {
            $code = 'WT-'.strtoupper(Str::random(6));
        } while (Booking::where('bookingCode', $code)->exists());

        return $code;
    }

    private function calculateTotalPriceAndCost(array &$data): array
    {
        $price = 0;
        $cost = 0;
        $pax = $data['metadata']['pax'] ?? 1;
        $paxChildren = $data['metadata']['paxChildren'] ?? 0;
        $selectedServices = $data['metadata']['selected_services'] ?? [];
        $serviceQty = (array) ($data['metadata']['service_qty'] ?? []);
        $pakaiVan = (bool) ($data['metadata']['use_van'] ?? false);

        // Bawaan NOL, bukan 11. Memungut 11% adalah keputusan yang hanya boleh
        // diambil sadar oleh pemilik -- memungut PPN tanpa berstatus PKP itu
        // masalah hukum, dan angka 11 yang tertanam di kode berarti setiap
        // pesanan sejak awal dipungut tanpa seorang pun pernah memilihnya.
        // Kalau nanti sudah PKP, isi Pengaturan > Keuangan > Persentase Pajak.
        $taxPercentage = 0;
        $surchargeWeekend = 0;
        $surchargePeak = 0;
        $peakStart = '';
        $peakEnd = '';

        $setting = \App\Models\Setting::where('key', 'general')->first();
        if ($setting && isset($setting->value['finance'])) {
            $taxPercentage = (float) ($setting->value['finance']['tax_percentage'] ?? 0);
            $surchargeWeekend = (float) ($setting->value['finance']['surcharge_weekend'] ?? 0);
            $surchargePeak = (float) ($setting->value['finance']['surcharge_peak'] ?? 0);
            $peakStart = $setting->value['finance']['surcharge_peak_start'] ?? '';
            $peakEnd = $setting->value['finance']['surcharge_peak_end'] ?? '';
        }

        if (isset($data['packageId'])) {
            $package = Package::find($data['packageId']);
            $pricePerPerson = $package->price ?? 0;
            $costPerPerson = $package->cost_price ?? 0;
            $childPricePerPerson = $package->childPrice ?? ($pricePerPerson * 0.5);

            // Punya harga grosir? Maka harga datang dari tier, titik. Aturan
            // pemilihannya hidup di satu tempat, Package::pricingTierFor(),
            // supaya kalkulator di kartu paket tidak bisa memakai aturan lain.
            //
            // Dewasa dan anak dihitung TERPISAH: tier harga dewasa dipilih dari
            // jumlah dewasa, tier harga anak dari jumlah anak. Menggabungkan
            // keduanya jadi satu ambang membuat menambah anak justru bisa
            // MENURUNKAN total tagihan -- rombongan yang lebih besar membayar
            // lebih murah daripada rombongan yang lebih kecil, dan tamu yang
            // membatalkan satu anak menerima harga yang naik.
            $adultTier = $package->pricingTierFor($pax);
            $childTier = $package->pricingTierFor($paxChildren);

            if ($adultTier !== null) {
                $pricePerPerson = $adultTier['price'] ?? $pricePerPerson;
            }
            if ($childTier !== null) {
                // Harga anak ikut tier juga. childPrice paket sengaja TIDAK
                // dipakai di sini: mencampur harga anak dasar dengan harga
                // dewasa tier membuat anak bisa lebih mahal daripada setengah
                // harga dewasa yang benar-benar dibayar. Kolomnya wajib diisi
                // di form admin; setengah harga dewasa tier-nya sendiri hanya
                // jaring pengaman untuk baris tier lama yang terlanjur kosong.
                $childPricePerPerson = $childTier['child_price']
                    ?? (($childTier['price'] ?? $pricePerPerson) * 0.5);
            }

            // Van MENGGANTIKAN tarif per pax, tidak menambahnya. Ambangnya
            // diperiksa ulang di sini, bukan dipercaya dari form: rombongan
            // kecil yang mengirim use_van=1 tetap membayar tarif Innova.
            $kendaraan = $package->vehicleOptionFor($pax);
            // Dua keadaan yang berbeda, jangan disatukan:
            //
            // WAJIB (pax melewati kapasitas Innova) -- tarifnya SUDAH ada di
            // tier 7+, karena band itu memang diisi tarif Van. Menimpanya lagi
            // dengan tarif Van 6-pax justru menagih kelebihan: di paket
            // transport, 8 pax jadi RM 565/org padahal band-nya RM 446.
            //
            // SUKARELA (rombongan masih muat Innova tapi minta Van demi bagasi)
            // -- di sini tarif Van memang menggantikan tarif tier.
            if ($pakaiVan && $kendaraan !== null && ! $kendaraan['wajib']) {
                $pricePerPerson = $kendaraan['price'];
                $childPricePerPerson = $pricePerPerson * 0.5;
            }

            $priceDewasa = $pricePerPerson * $pax;
            $priceAnak = $childPricePerPerson * $paxChildren;
            
            $additionalServicesPrice = 0;
            // No configured extras means no extras. The old placeholder here
            // ("Private Jet Charter", 120000000) was a demo leftover that could
            // be selected and billed.
            $availableServices = $package->pricingDetails['additional_services'] ?? [];
            
            $detailedServices = [];
            foreach ($availableServices as $srv) {
                if (in_array($srv['name'], $selectedServices)) {
                    // Tarifnya PER PAX, dan jumlah pesertanya belum tentu
                    // serombongan: Hiking Sibayak sering cuma sebagian yang naik.
                    //
                    // Angka dari form DIJEPIT ke jumlah tamu. Tanpa itu, satu
                    // permintaan yang disusun tangan bisa mengirim qty raksasa
                    // (menagih tamu kelewat mahal) atau nol (jasa ikut jalan
                    // tanpa dibayar). Tidak ada qty sama sekali -> jatuh ke
                    // seluruh rombongan, perilaku lama.
                    $jiwa = $pax + $paxChildren;
                    $diminta = $serviceQty[$srv['name']] ?? null;
                    $qty = $diminta === null ? $jiwa : max(0, min((int) $diminta, $jiwa));

                    if ($qty === 0) {
                        continue;
                    }

                    $srvUnit = (float) ($srv['price'] ?? 0);
                    $srvPrice = $srvUnit * $qty;
                    $additionalServicesPrice += $srvPrice;
                    $detailedServices[] = [
                        'name' => $srv['name'],
                        'unit_price' => $srvUnit,
                        'pax' => $qty,
                        'price' => $srvPrice,
                    ];
                }
            }

            $totalSebelumPajak = $priceDewasa + $priceAnak + $additionalServicesPrice;

            // Apply Surcharges
            $surchargeAmount = 0;
            $appliedSurcharges = [];
            $startDateObj = isset($data['startDate']) ? \Carbon\Carbon::parse($data['startDate']) : null;

            if ($startDateObj) {
                // 1. Check Weekend
                if ($surchargeWeekend > 0 && $startDateObj->isWeekend()) {
                    $amt = $totalSebelumPajak * ($surchargeWeekend / 100);
                    $surchargeAmount += $amt;
                    $appliedSurcharges[] = [
                        'name' => "Weekend Surcharge ({$surchargeWeekend}%)",
                        'amount' => $amt
                    ];
                }

                // 2. Check Peak Season
                if ($surchargePeak > 0 && !empty($peakStart) && !empty($peakEnd)) {
                    try {
                        $currentYear = $startDateObj->year;
                        $startParts = explode('/', $peakStart);
                        $endParts = explode('/', $peakEnd);
                        
                        if (count($startParts) == 2 && count($endParts) == 2) {
                            $peakStartDate = \Carbon\Carbon::createFromDate($currentYear, $startParts[1], $startParts[0])->startOfDay();
                            $peakEndDate = \Carbon\Carbon::createFromDate($currentYear, $endParts[1], $endParts[0])->endOfDay();

                            // Handle year rollover (e.g. 20/12 to 05/01)
                            if ($peakEndDate->lt($peakStartDate)) {
                                if ($startDateObj->month <= $peakEndDate->month) {
                                    $peakStartDate->subYear();
                                } else {
                                    $peakEndDate->addYear();
                                }
                            }

                            if ($startDateObj->between($peakStartDate, $peakEndDate)) {
                                $amt = $totalSebelumPajak * ($surchargePeak / 100);
                                $surchargeAmount += $amt;
                                $appliedSurcharges[] = [
                                    'name' => "Peak Season Surcharge ({$surchargePeak}%)",
                                    'amount' => $amt
                                ];
                            }
                        }
                    } catch (\Exception $e) {
                        Log::warning('Failed to parse peak season dates: ' . $e->getMessage());
                    }
                }
            }

            $totalDenganSurcharge = $totalSebelumPajak + $surchargeAmount;
            // 2 decimals: prices are in ringgit now, so rounding to whole units
            // would discard sen on every booking.
            $pajakLayanan = round($totalDenganSurcharge * ($taxPercentage / 100), 2);
            $totalAkhir = $totalDenganSurcharge + $pajakLayanan;

            $price = $totalAkhir;
            $cost = ($costPerPerson * $pax) + ($costPerPerson * 0.5 * $paxChildren);

            $data['metadata']['price_breakdown'] = [
                'pax_dewasa' => $pax,
                'price_dewasa_total' => $priceDewasa,
                'pax_anak' => $paxChildren,
                'price_anak_total' => $priceAnak,
                'additional_services' => $detailedServices,
                'subtotal_base' => $totalSebelumPajak,
                'surcharges' => $appliedSurcharges,
                'total_surcharge' => $surchargeAmount,
                'subtotal_with_surcharge' => $totalDenganSurcharge,
                'tax_percentage' => $taxPercentage,
                'tax' => $pajakLayanan,
                'total' => $totalAkhir
            ];
        }

        return ['price' => $price, 'cost' => $cost];
    }

    public function getAll(array $filters = [], bool $noPagination = false)
    {
        return $this->repository->getAll($filters, $noPagination);
    }

    public function find(int $id)
    {
        return $this->repository->find($id);
    }
}
