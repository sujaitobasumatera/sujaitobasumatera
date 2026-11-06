<?php

namespace App\Http\Requests;

use App\Models\Setting;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Lead time minimal (hari) sebelum keberangkatan, dari booking_settings.
     * Sengaja fallback ke 1 bila setting belum ada — jangan pernah izinkan pesan
     * untuk hari yang sama tanpa keputusan sadar.
     */
    protected function minAdvanceDays(): int
    {
        try {
            $settings = optional(Setting::where('key', 'booking_settings')->first())->value ?? [];
        } catch (\Throwable $e) {
            return 1;
        }

        $days = $settings['min_advance_days'] ?? 1;

        return is_numeric($days) && (int) $days >= 0 ? (int) $days : 1;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $minDate = now()->addDays($this->minAdvanceDays())->format('Y-m-d');

        return [
            'packageId' => 'required|exists:packages,id',
            'customerName' => 'required|string|max:255',
            // Opsional. Pasarnya berjalan lewat WhatsApp, dan email tidak
            // pernah dipakai menghubungi tamu -- notifikasi booking hanya ke
            // admin. Menjadikannya wajib berarti kehilangan calon tamu di
            // kolom yang tidak pernah kita baca. Pengenal pelanggan kini
            // nomor telepon (Customer::phoneKey).
            'customerEmail' => 'nullable|email|max:255',
            // Telepon: hanya angka, spasi, +, -, (), 7–20 karakter. Menutup "abc"
            // yang lolos lalu bikin konfirmasi WhatsApp gagal.
            'customerPhone' => ['required', 'string', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            // Lead time ditegakkan: tidak boleh sebelum min_advance_days dari hari ini.
            'startDate' => 'required|date|after_or_equal:'.$minDate,
            // Batas atas peserta — tanpa ini bisa dikirim 99999.
            'pax' => 'required|integer|min:1|max:99',
            'paxChildren' => 'nullable|integer|min:0|max:99',
            'selected_services' => 'nullable|array',
            'selected_services.*' => 'string',
            // Jumlah peserta per layanan, berkunci nama layanan. Dijepit ulang
            // ke jumlah tamu di BookingService -- ini cuma penjaga bentuknya.
            'service_qty' => 'nullable|array',
            'service_qty.*' => 'nullable|integer|min:0|max:999',
            // Sekadar niat tamu. Kelayakannya (ambang pax) diperiksa ulang di
            // BookingService -- form boleh berbohong, harga tidak boleh.
            'use_van' => 'nullable|boolean',
            'notes' => 'nullable|string|max:2000',
            // Centang S&K dihapus atas permintaan pemilik: satu tindakan lagi
            // sebelum memesan. Persetujuannya TIDAK hilang -- ia pindah jadi
            // pemberitahuan di atas tombol kirim ("dengan menekan Pesan, Anda
            // menyetujui ..."), pola yang lazim dan tetap merupakan tindakan
            // afirmatif. Relevan UU PDP 27/2022 dan PDPA untuk tamu SG/MY;
            // kalau bentuk persetujuannya perlu lebih tegas, kembalikan
            // 'terms' => 'accepted' di sini dan centangnya di form.
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'packageId.exists' => 'Paket wisata yang dipilih tidak tersedia.',
            'startDate.after_or_equal' => 'Pemesanan minimal '.$this->minAdvanceDays().' hari sebelum keberangkatan.',
            'customerEmail.email' => 'Format email tidak valid.',
            'customerPhone.regex' => 'Nomor telepon tidak valid. Gunakan angka, boleh diawali +.',
            'pax.max' => 'Jumlah peserta terlalu besar. Hubungi kami untuk rombongan besar.',
        ];
    }
}
