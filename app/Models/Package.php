<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Helpers\CurrencyHelper;
use App\Traits\HasImageFallback;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin \Eloquent
 */

class Package extends Model
{
    use HasFactory;
    use \App\Traits\Syncable, HasImageFallback, SoftDeletes;

    const CREATED_AT = 'createdAt';

    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'slug', 'name', 'shortDescription', 'description', 'locationTag',
        'price', 'childPrice', 'cost_price', 'priceDisplay', 'duration', 'images',
        'videos', 'brochure', 'priceImage', 'mapEmbed', 'accommodations', 'highlights',
        'includes', 'excludes', 'pricingDetails', 'itinerary', 'itineraryText',
        'dronePrice', 'droneLocation', 'notes', 'status', 'isFeatured',
        'sortOrder', 'metaTitle', 'metaDescription',
        'translations', 'cityId',
    ];

    protected $appends = ['first_image', 'image_url', 'price_image_url', 'formatted_price', 'translated_name', 'translated_description', 'translated_short_description', 'translated_itinerary_text'];

    protected $casts = [
        'images' => 'array',
        'videos' => 'array',
        'accommodations' => 'array',
        'highlights' => 'array',
        'includes' => 'array',
        'excludes' => 'array',
        'pricingDetails' => 'array',
        'itinerary' => 'array',
        'translations' => 'array',
        'isFeatured' => 'boolean',
        'price' => 'double',
        'childPrice' => 'double',
        'dronePrice' => 'double',
    ];

    /**
     * Daftar video paket yang sudah siap dirender.
     *
     * Admin boleh menempel tautan ATAU mengunggah berkas, jadi normalisasinya
     * dikerjakan di satu tempat: halaman detail berform dan halaman detail
     * tanpa form membaca hasil yang sama persis.
     *
     * Tautan yang tidak dikenali DIBUANG, bukan diteruskan apa adanya. Nilai
     * ini masuk ke atribut src <iframe>; meloloskan string sembarang dari form
     * admin membuka jalan javascript: dan data: URI.
     *
     * @return array<int, array{kind: string, url: string, title: string, gear: string}>
     */
    public function videoList(): array
    {
        $out = [];

        foreach ((array) ($this->videos ?? []) as $row) {
            if (! is_array($row)) {
                continue;
            }

            $src = trim((string) ($row['src'] ?? ''));
            if ($src === '') {
                continue;
            }

            $title = trim((string) ($row['title'] ?? ''));
            // Alat rekam. Yang menjual jasa dokumentasi bukan videonya sendiri,
            // melainkan bukti bahwa itu rekaman tim ini -- bukan stok.
            $gear = trim((string) ($row['gear'] ?? ''));

            // Berkas unggahan: diputar pemutar bawaan browser, bukan iframe.
            if (($row['type'] ?? 'link') === 'file') {
                $out[] = ['kind' => 'file', 'url' => Storage::disk('public')->url($src), 'title' => $title, 'gear' => $gear];

                continue;
            }

            if ($embed = self::videoEmbedUrl($src)) {
                $out[] = ['kind' => 'embed', 'url' => $embed, 'title' => $title, 'gear' => $gear];

                continue;
            }

            // Tautan langsung ke berkas video (mis. CDN) tetap bisa diputar
            // pemutar bawaan, selama skemanya http(s).
            if (preg_match('~^https?://~i', $src) && preg_match('~\.(mp4|webm|ogg)(\?.*)?$~i', $src)) {
                $out[] = ['kind' => 'file', 'url' => $src, 'title' => $title, 'gear' => $gear];
            }
        }

        return $out;
    }

    /**
     * Daftar penginapan per malam, sudah bersih dan berurutan.
     *
     * Baris tanpa nama hotel dibuang: kartu "Malam 2" yang kosong lebih buruk
     * daripada tidak ada kartunya sama sekali -- tamu membacanya sebagai
     * "belum diputuskan".
     *
     * @return array<int, array{night: int, name: string, class: string, image: string|null}>
     */
    /**
     * Pembeda khusus paket ini, sudah siap dirender.
     *
     * Baris tanpa judul dibuang dengan alasan yang sama seperti penginapan:
     * poin kosong terbaca sebagai janji yang belum selesai ditulis, dan itu
     * lebih buruk daripada tidak ada poinnya sama sekali.
     *
     * Kosong berarti "belum diisi", dan pemanggilnya jatuh ke poin situs --
     * BUKAN menampilkan blok kosong.
     *
     * @return array<int, array{title: string, text: string}>
     */
    public function highlightList(): array
    {
        $out = [];

        foreach ((array) ($this->highlights ?? []) as $row) {
            if (! is_array($row)) {
                continue;
            }

            $title = trim((string) ($row['title'] ?? ''));
            if ($title === '') {
                continue;
            }

            $out[] = [
                'title' => $title,
                'text' => trim((string) ($row['text'] ?? '')),
            ];
        }

        return $out;
    }

    public function accommodationList(): array
    {
        $out = [];

        foreach ((array) ($this->accommodations ?? []) as $i => $row) {
            if (! is_array($row)) {
                continue;
            }

            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $image = trim((string) ($row['image'] ?? ''));

            // Video penginapan per malam. Dinormalkan di sini, sama seperti
            // videoList(): tautan yang tidak dikenali DIBUANG, bukan diteruskan
            // apa adanya -- nilai ini masuk ke atribut src <iframe>.
            $video = trim((string) ($row['video'] ?? ''));
            $videoEmbed = $video !== '' ? self::videoEmbedUrl($video) : null;

            $out[] = [
                'night' => (int) ($row['night'] ?? 0) ?: count($out) + 1,
                'name' => $name,
                'class' => trim((string) ($row['class'] ?? '')),
                'image' => $image !== '' ? $image : null,
                'video' => $videoEmbed,
            ];
        }

        usort($out, fn ($a, $b) => $a['night'] <=> $b['night']);

        return $out;
    }

    /**
     * URL sematan untuk tautan YouTube/Vimeo. null bila bukan keduanya.
     */
    public static function videoEmbedUrl(string $url): ?string
    {
        if (preg_match('~(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|live/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{6,})~i', $url, $m)) {
            return 'https://www.youtube-nocookie.com/embed/'.$m[1];
        }

        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~i', $url, $m)) {
            return 'https://player.vimeo.com/video/'.$m[1];
        }

        return null;
    }

    /**
     * URL sematan peta lokasi, atau null bila kolomnya kosong/tak dikenali.
     *
     * Admin boleh menempel kode <iframe> utuh dari Google Maps, tautan biasa,
     * atau sepasang koordinat. Yang diambil hanya URL-nya lalu diperiksa
     * host-nya -- kode <iframe> mentah TIDAK pernah dicetak ke halaman, karena
     * itu berarti satu akun admin yang jebol bisa menanam skrip di halaman
     * publik yang paling ramai.
     */
    public function mapEmbedUrl(): ?string
    {
        $raw = trim((string) ($this->mapEmbed ?? ''));
        if ($raw === '') {
            return null;
        }

        // Ambil src bila yang ditempel kode <iframe> utuh.
        if (preg_match('~<iframe[^>]*\ssrc=["\']([^"\']+)["\']~i', $raw, $m)) {
            $raw = html_entity_decode($m[1]);
        }

        // Sepasang koordinat: -2.6845, 98.8756
        if (preg_match('~^\s*(-?\d{1,3}(?:\.\d+)?)\s*,\s*(-?\d{1,3}(?:\.\d+)?)\s*$~', $raw, $m)) {
            return 'https://maps.google.com/maps?q='.$m[1].','.$m[2].'&z=14&output=embed';
        }

        if (preg_match('~^https?://~i', $raw)) {
            $host = strtolower((string) parse_url($raw, PHP_URL_HOST));
            // Hanya domain peta Google. Selain itu diabaikan diam-diam:
            // halaman tetap tampil, cuma tanpa peta.
            if ($host !== '' && preg_match('~(^|\.)(google\.[a-z.]+|goo\.gl)$~', $host)) {
                return $raw;
            }
        }

        return null;
    }

    /**
     * URL unduhan brosur, atau null bila belum diunggah.
     */
    public function brochureUrl(): ?string
    {
        $path = trim((string) ($this->brochure ?? ''));

        return $path === '' ? null : Storage::disk('public')->url($path);
    }

    /**
     * URL gambar informasi harga, atau null bila belum diunggah.
     *
     * Sengaja TIDAK lewat imageUrl(): helper itu memulangkan gambar
     * placeholder saat path kosong, dan bingkai harga harus benar-benar
     * hilang kalau adminnya belum mengunggah apa pun -- bukan menampilkan
     * kotak berisi gambar pengganti.
     */
    public function getPriceImageUrlAttribute(): ?string
    {
        $path = trim((string) ($this->priceImage ?? ''));

        return $path === '' ? null : Storage::disk('public')->url($path);
    }

    /**
     * Tier harga grosir yang berlaku untuk sejumlah peserta.
     *
     * Dipanggil DUA KALI oleh BookingService: sekali dengan jumlah dewasa
     * untuk memilih harga dewasa, sekali dengan jumlah anak untuk memilih
     * harga anak. Keduanya dihitung terpisah — satu ambang gabungan
     * (dewasa + anak) membuat menambah satu anak justru bisa MENURUNKAN total
     * tagihan, karena penghematan tier bisa melebihi harga anak itu sendiri.
     *
     * Aturannya: kalau paket ini punya harga grosir, harga SELALU datang dari
     * salah satu tier. Sebelumnya jumlah pax yang jatuh di celah antar-tier
     * (mis. tier 1-9 dan 11-15, lalu tamu memesan 10) tidak cocok dengan tier
     * mana pun, tidak pula melampaui tier tertinggi, sehingga diam-diam dibayar
     * dengan harga dasar paket — angka yang mungkin sudah lama tidak diurus.
     * Tidak ada gejalanya: halaman tetap terlihat wajar dengan harga yang salah.
     *
     * Mengembalikan null hanya bila paket ini memang tidak punya tier.
     *
     * @return array<string, mixed>|null
     */
    public function pricingTierFor(int $pax): ?array
    {
        $tiers = [];
        foreach ($this->pricingDetails['tiers'] ?? [] as $tier) {
            // Baris tak lengkap dibuang: membandingkan pax dengan null selalu
            // menghasilkan kecocokan palsu pada tier pertama.
            if (is_array($tier) && isset($tier['min_pax'], $tier['max_pax'])) {
                $tiers[] = $tier;
            }
        }

        if ($tiers === []) {
            return null;
        }

        // Cocok persis. Urutan asli dipertahankan: bila dua tier bertumpuk,
        // yang ditulis lebih dulu yang menang — sama seperti sebelumnya.
        foreach ($tiers as $tier) {
            if ($pax >= $tier['min_pax'] && $pax <= $tier['max_pax']) {
                return $tier;
            }
        }

        $highest = $tiers[0];
        $lowest = $tiers[0];
        foreach ($tiers as $tier) {
            if ($tier['max_pax'] > $highest['max_pax']) {
                $highest = $tier;
            }
            if ($tier['min_pax'] < $lowest['min_pax']) {
                $lowest = $tier;
            }
        }

        // Rombongan lebih besar dari tier tertinggi: pakai tier tertinggi.
        if ($pax > $highest['max_pax']) {
            return $highest;
        }

        // Lebih kecil dari tier terendah: pakai tier terendah.
        if ($pax < $lowest['min_pax']) {
            return $lowest;
        }

        // Jatuh di celah: pakai tier terdekat DI BAWAHNYA. Diskon grosir baru
        // berlaku setelah ambangnya benar-benar tercapai — 10 pax di antara
        // tier 1-9 dan 11-15 membayar harga 1-9, bukan harga diskon 11-15.
        $below = null;
        foreach ($tiers as $tier) {
            if ($tier['max_pax'] < $pax && ($below === null || $tier['max_pax'] > $below['max_pax'])) {
                $below = $tier;
            }
        }

        return $below ?? $lowest;
    }

    /**
     * Pilihan kendaraan besar, bila rombongannya sudah cukup besar untuk memilih.
     *
     * Van BUKAN biaya tambahan. Di daftar harga operator, "6 pax pakai Van"
     * adalah tarif per pax TERSENDIRI yang menggantikan tarif Innova -- 4D3N
     * enam orang: RM 700/pax dengan Van, bukan RM 600/pax lalu ditambah RM 700
     * sekali. Selama Van masih disimpan di `additional_services`, setiap
     * pemesanan Van ditagih terlalu mahal, dan tidak ada yang menandainya.
     *
     * Mengembalikan null bila paket ini tidak punya pilihan Van, atau bila
     * rombongannya belum mencapai ambang -- pilihannya memang tidak ditawarkan
     * ke rombongan kecil.
     *
     * `wajib` menyala begitu rombongan melewati kapasitas Innova: 7 orang tidak
     * muat di mobil 6-7 kursi, jadi Van bukan lagi pilihan gaya hidup melainkan
     * kenyataan. Selama ia masih bisa dibatalkan, satu rombongan 8 orang tinggal
     * tidak mencentangnya, membayar tarif Innova, lalu datang butuh Van --
     * selisihnya RM 800-1.000 per pemesanan dan tidak ada yang menandainya.
     *
     * @return array{name: string, min_pax: int, price: float, wajib: bool}|null
     */
    public function vehicleOptionFor(int $pax): ?array
    {
        $v = $this->pricingDetails['vehicle'] ?? null;

        if (! is_array($v) || ! isset($v['price'])) {
            return null;
        }

        $minPax = (int) ($v['min_pax'] ?? 6);

        if ($pax < $minPax) {
            return null;
        }

        // Bawaan minPax + 1: Van boleh DIPILIH sejak rombongan cukup besar untuk
        // butuh bagasi lega, tapi baru WAJIB begitu kursinya benar-benar habis.
        $wajibDari = (int) ($v['wajib_dari'] ?? ($minPax + 1));

        return [
            'name' => trim((string) ($v['name'] ?? 'Van')),
            'min_pax' => $minPax,
            'price' => (float) $v['price'],
            'wajib' => $pax >= $wajibDari,
        ];
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'cityId');
    }

    public function cities()
    {
        return $this->belongsToMany(City::class, 'city_package', 'package_id', 'city_id');
    }

    public function packageImages()
    {
        return $this->hasMany(PackageImage::class)->orderBy('sort_order');
    }

    public function amenities()
    {
        return $this->hasMany(PackageAmenity::class);
    }

    public function packageIncludes()
    {
        return $this->hasMany(PackageAmenity::class)->where('type', 'include');
    }

    public function packageExcludes()
    {
        return $this->hasMany(PackageAmenity::class)->where('type', 'exclude');
    }

    public function getFirstImageAttribute()
    {
        $images = $this->images;
        if (is_string($images)) {
            $images = json_decode($images, true);
        }

        return $this->resolveImageUrl($images[0] ?? null);
    }

    public function getImageUrlAttribute()
    {
        return $this->first_image;
    }

    public function getFormattedPriceAttribute()
    {
        return CurrencyHelper::formatPrice($this->price);
    }

    // Dynamic Localization Accessors
    public function getTranslatedNameAttribute()
    {
        $locale = app()->getLocale();
        return $this->translations[$locale]['name'] ?? $this->name;
    }

    public function getTranslatedDescriptionAttribute()
    {
        $locale = app()->getLocale();
        return $this->translations[$locale]['description'] ?? $this->description;
    }

    public function getTranslatedShortDescriptionAttribute()
    {
        $locale = app()->getLocale();
        return $this->translations[$locale]['shortDescription'] ?? $this->shortDescription;
    }

    public function getTranslatedItineraryTextAttribute()
    {
        $locale = app()->getLocale();
        return $this->translations[$locale]['itineraryText'] ?? $this->itineraryText;
    }
}
