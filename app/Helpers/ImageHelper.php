<?php

use App\Helpers\CurrencyHelper;
use App\Models\Blog;
use App\Models\Media;
use App\Models\Package;
use Illuminate\Support\Facades\Storage;

// Loaded here (not via composer "files") because the FTP deploy excludes
// composer.json/vendor and never runs dump-autoload on the server — this
// file is already registered, so requiring siblings keeps helpers reachable.
require_once __DIR__.'/RatingHelper.php';

/**
 * Batas unggah efektif server, dalam KB.
 *
 * PHP menolak berkas yang melewati upload_max_filesize/post_max_size SEBELUM
 * Laravel sempat memvalidasi: form-nya memantul tanpa pesan kesalahan yang
 * bisa dipahami admin. Jadi aturan validasi dan tulisan di form harus mengutip
 * angka server yang sebenarnya, bukan angka yang kita harapkan.
 *
 * @param  int  $ceilingKb  batas atas yang kita mau, misal 50 MB untuk video
 */
if (! function_exists('maxUploadKb')) {
    function maxUploadKb(int $ceilingKb = 51200): int
    {
        $toKb = static function (string $val): int {
            $val = trim($val);
            if ($val === '' || $val === '0') {
                return PHP_INT_MAX; // 0 = tanpa batas
            }
            $unit = strtolower(substr($val, -1));
            $num = (float) $val;

            return (int) match ($unit) {
                'g' => $num * 1024 * 1024,
                'm' => $num * 1024,
                'k' => $num,
                default => $num / 1024,
            };
        };

        return max(1, min(
            $ceilingKb,
            $toKb((string) ini_get('upload_max_filesize')),
            $toKb((string) ini_get('post_max_size'))
        ));
    }
}

/**
 * Global Image Path Resolution Helper
 *
 * Single source of truth for resolving storage/asset paths to full URLs.
 * Replaces all inline path resolution logic scattered across Blade views.
 *
 * Usage in Blade:
 *   {{ imageUrl($path) }}
 *   {{ imageUrl($path, asset('images/custom-fallback.webp')) }}
 *
 * Usage in PHP (Controller/Service):
 *   imageUrl($path)
 */
if (! function_exists('imageUrl')) {
    function imageUrl(?string $path, ?string $fallback = null): string
    {
        // Request-level memo: this resolver runs several file_exists()/Storage::exists()
        // stat calls and is invoked for every image in every loop. A given (path, fallback)
        // resolves identically within one request, so cache it to avoid repeating the I/O.
        static $memo = [];
        $memoKey = ($path ?? "\0").'|'.($fallback ?? "\0");
        if (array_key_exists($memoKey, $memo)) {
            return $memo[$memoKey];
        }

        return $memo[$memoKey] = (function () use ($path, $fallback): string {
        // If path is null or empty, use fallback if specified, otherwise default local asset
        if (empty($path) || $path === 'null') {
            if ($fallback) {
                $path = $fallback;
            } else {
                return asset('images/home/tour.webp');
            }
        }

        $lower = strtolower($path);

        // BERKAS NYATA MENANG. Seluruh pemetaan kata kunci di bawah ini adalah
        // jaring pengaman untuk path warisan yang berkasnya sudah tidak ada
        // (mis. `assets/images/packages/toba-1.jpg`), bukan aturan penggantian.
        //
        // Dulu urutannya terbalik: kata kunci diperiksa lebih dulu, pemeriksaan
        // berkas paling akhir. Akibatnya foto asli yang benar-benar diunggah
        // pun dibajak begitu namanya memuat 'toba', 'samosir', 'medan', dan
        // seterusnya -- admin mengunggah foto hotel bernama "danau-toba-1.jpg",
        // yang muncul di halaman justru gambar bawaan. Tidak ada gejalanya:
        // halaman tetap menampilkan gambar yang terlihat wajar.
        //
        // Hanya berlaku untuk path lokal. URL penuh, data:, dan blob: tetap
        // diurus oleh cabangnya sendiri di bawah.
        if (! preg_match('~^(https?:)?//|^(data|blob):~i', $path)) {
            $nyata = ltrim($path, '/');
            if (str_starts_with($nyata, '_static/')) {
                $nyata = substr($nyata, 8);
            }
            $tanpaStorage = str_starts_with($nyata, 'storage/') ? substr($nyata, 8) : $nyata;

            if ($nyata !== '' && file_exists(public_path($nyata)) && ! is_dir(public_path($nyata))) {
                return asset($nyata);
            }
            if ($tanpaStorage !== '' && Storage::disk('public')->exists($tanpaStorage)) {
                return Storage::disk('public')->url($tanpaStorage);
            }
        }

        // Special local fallback keys or avatar keywords
        if (str_contains($lower, 'staff1')) {
            return asset('images/sumut/specialist_avatar.webp');
        }
        if (str_contains($lower, 'user1')) {
            return asset('images/sumut/avatar_user_1.webp');
        }
        if (str_contains($lower, 'user2')) {
            return asset('images/sumut/avatar_user_2.webp');
        }
        if (str_contains($lower, 'user3')) {
            return asset('images/sumut/avatar_user_3.webp');
        }
        if (str_contains($lower, 'user4')) {
            return asset('images/sumut/avatar_user_4.webp');
        }
        if (str_contains($lower, 'outbound')) {
            return asset('images/home/outbound.webp');
        }
        if (str_contains($lower, 'tour')) {
            return asset('images/home/tour.webp');
        }

        // Intercept legacy DB paths (2023/10/...) first to assign varied premium local images
        if (str_contains($lower, '2023/10/') || preg_match('/assets\/images\/\d{4}\/\d{2}\//', $lower)) {
            if (str_contains($lower, '001-1')) {
                return asset('images/sumut/toba_hero.webp');
            }
            if (str_contains($lower, '002-1')) {
                return asset('images/sumut/toba_landscape.webp');
            }
            if (str_contains($lower, '003-1')) {
                return asset('images/sumut/batak_house.webp');
            }
            if (str_contains($lower, '004')) {
                return asset('images/sumut/sipiso_piso.webp');
            }
            if (str_contains($lower, '005')) {
                return asset('images/sumut/berastagi.webp');
            }
            if (str_contains($lower, '006')) {
                return asset('images/sumut/lumbini.webp');
            }
            if (str_contains($lower, '008')) {
                return asset('images/sumut/hotel_room.webp');
            }
            if (str_contains($lower, '009-1')) {
                return asset('images/sumut/maimun_palace.webp');
            }
            if (str_contains($lower, '0010') || str_contains($lower, '010')) {
                return asset('images/sumut/masjid_raya.webp');
            }
            if (str_contains($lower, 'team-building')) {
                return asset('images/home/outbound.webp');
            }
            if (str_contains($lower, 'fun-games')) {
                return asset('images/home/outbound.webp');
            }
            if (str_contains($lower, 'gathering')) {
                return asset('images/home/outbound.webp');
            }
            if (str_contains($lower, 'outbound-kids')) {
                return asset('images/home/outbound.webp');
            }
        }

        // Intercept Unsplash, Pravatar, Google Content, or remote placeholders to serve local premium assets instead
        if (
            str_contains($lower, 'unsplash.com') ||
            str_contains($lower, 'placeholder') ||
            str_contains($lower, 'pravatar.cc') ||
            str_contains($lower, 'googleusercontent.com')
        ) {
            if (str_contains($lower, 'photo-1580489944761') || str_contains($lower, 'staff1')) {
                return asset('images/sumut/specialist_avatar.webp');
            }
            if (str_contains($lower, 'photo-1507003211169') || str_contains($lower, 'user1') || str_contains($lower, 'ab6axubc2hfgasrsa7a85bf12siuk3')) {
                return asset('images/sumut/avatar_user_1.webp');
            }
            if (str_contains($lower, 'photo-1534528741775') || str_contains($lower, 'user2') || str_contains($lower, 'ab6axuafawoa9yazv80gupi35ev08b')) {
                return asset('images/sumut/avatar_user_2.webp');
            }
            if (str_contains($lower, 'photo-1500648767791') || str_contains($lower, 'user3')) {
                return asset('images/sumut/avatar_user_3.webp');
            }
            if (str_contains($lower, 'photo-1494790108377') || str_contains($lower, 'user4')) {
                return asset('images/sumut/avatar_user_4.webp');
            }
            if (str_contains($lower, 'photo-1472099645785')) {
                return asset('images/sumut/avatar_user_1.webp');
            }
            if (str_contains($lower, 'photo-1596402184320') || str_contains($lower, 'photo-1544735049') || str_contains($lower, 'photo-1511632765')) {
                return asset('images/sumut/sumatra_panorama.webp');
            }
            if (str_contains($lower, 'googleusercontent.com')) {
                return asset('images/sumut/avatar_user_3.webp');
            }

            return asset('images/home/tour.webp');
        }

        // Already a full external URL — return as-is (except if it is one of the intercepted domains above)
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '//')) {
            return $path;
        }

        // Already a data URI or blob — return as-is
        if (str_starts_with($path, 'data:') || str_starts_with($path, 'blob:')) {
            return $path;
        }

        // Intercept Partner Logos first to prevent 'toba' substring matching
        if (str_contains($lower, 'mandiri')) {
            return asset('images/partners/mandiri.svg');
        }
        if (str_contains($lower, 'usu-') || str_contains($lower, 'usu.')) {
            return asset('images/partners/usu.svg');
        }
        if (str_contains($lower, 'pelindo')) {
            return asset('images/partners/pelindo.svg');
        }
        if (str_contains($lower, 'hyundai')) {
            return asset('images/partners/hyundai.svg');
        }

        // Premium Localized Overrides for North Sumatra Tourism Images
        if (str_contains($lower, 'lake-toba-premium')) {
            return asset('images/sumut/toba_hero.webp');
        }
        if (str_contains($lower, 'sumatra-panorama')) {
            return asset('images/sumut/sumatra_panorama.webp');
        }

        // Multi-image Package overrides to ensure variety on details page
        if (str_contains($lower, 'toba-1')) {
            return asset('images/sumut/toba_hero.webp');
        }
        if (str_contains($lower, 'toba-2') || str_contains($lower, 'toba-landscape')) {
            return asset('images/sumut/toba_landscape.webp');
        }
        if (str_contains($lower, 'toba-3')) {
            return asset('images/sumut/batak_house.webp');
        }
        if (str_contains($lower, 'toba-4') || str_contains($lower, 'sipiso_piso') || str_contains($lower, 'sipiso-piso')) {
            return asset('images/sumut/sipiso_piso.webp');
        }
        if (str_contains($lower, 'toba') || str_contains($lower, 'samosir') || str_contains($lower, 'parapat')) {
            if (str_contains($lower, 'sunset')) {
                return asset('images/sumut/toba_landscape.webp');
            }
            if (str_contains($lower, 'boat') || str_contains($lower, 'danau-toba-panorama')) {
                return asset('images/sumut/toba_hero.webp');
            }
            if (str_contains($lower, 'huta-bolon') || str_contains($lower, 'batak')) {
                return asset('images/sumut/batak_house.webp');
            }

            return asset('images/sumut/toba_landscape.webp');
        }
        if (str_contains($lower, 'sipiso')) {
            return asset('images/sumut/sipiso_piso.webp');
        }
        if (str_contains($lower, 'berastagi-1')) {
            return asset('images/sumut/berastagi.webp');
        }
        if (str_contains($lower, 'berastagi-2') || str_contains($lower, 'lumbini')) {
            return asset('images/sumut/lumbini.webp');
        }
        if (str_contains($lower, 'berastagi-3') || str_contains($lower, 'simalem')) {
            return asset('images/sumut/hotel_room.webp');
        }
        if (str_contains($lower, 'berastagi') || str_contains($lower, 'karo')) {
            return asset('images/sumut/berastagi.webp');
        }
        if (str_contains($lower, 'medan-1') || str_contains($lower, 'maimun')) {
            return asset('images/sumut/maimun_palace.webp');
        }
        if (str_contains($lower, 'medan-2') || str_contains($lower, 'masjid')) {
            return asset('images/sumut/masjid_raya.webp');
        }
        if (str_contains($lower, 'medan')) {
            return asset('images/sumut/maimun_palace.webp');
        }
        if (str_contains($lower, 'bukitlawang-1') || str_contains($lower, 'orangutan')) {
            return asset('images/sumut/orangutan.webp');
        }
        if (str_contains($lower, 'bukitlawang-2') || str_contains($lower, 'bukit-lawang')) {
            return asset('images/sumut/sumatra_panorama.webp');
        }
        if (str_contains($lower, 'honeymoon-1')) {
            return asset('images/sumut/hotel_room.webp');
        }
        if (str_contains($lower, 'honeymoon-2')) {
            return asset('images/sumut/toba_landscape.webp');
        }
        if (str_contains($lower, 'honeymoon-3')) {
            return asset('images/sumut/toba_hero.webp');
        }
        if (str_contains($lower, 'sumut-complete-1')) {
            return asset('images/sumut/toba_hero.webp');
        }
        if (str_contains($lower, 'sumut-complete-2')) {
            return asset('images/sumut/berastagi.webp');
        }
        if (str_contains($lower, 'sumut-complete-3')) {
            return asset('images/sumut/orangutan.webp');
        }
        if (str_contains($lower, '010') || str_contains($lower, '0010') || str_contains($lower, 'hotel') || str_contains($lower, 'room')) {
            return asset('images/sumut/hotel_room.webp');
        }
        if (str_contains($lower, 'car') || str_contains($lower, 'avanza') || str_contains($lower, 'innova') || str_contains($lower, 'hiace') || str_contains($lower, 'alphard') || str_contains($lower, 'apv') || str_contains($lower, 'sigra')) {
            if (str_contains($lower, 'avanza') || str_contains($lower, 'apv') || str_contains($lower, 'sigra')) {
                return asset('images/sumut/car_avanza.webp');
            }
            if (str_contains($lower, 'innova')) {
                return asset('images/sumut/car_innova.webp');
            }
            if (str_contains($lower, 'hiace')) {
                return asset('images/sumut/car_hiace.webp');
            }
            if (str_contains($lower, 'alphard')) {
                return asset('images/sumut/car_alphard.webp');
            }

            return asset('images/sumut/car_avanza.webp');
        }

        if (str_contains($lower, 'avatar') || str_contains($lower, 'specialist') || str_contains($lower, 'sarah')) {
            if (str_contains($lower, 'specialist') || str_contains($lower, 'sarah')) {
                return asset('images/sumut/specialist_avatar.webp');
            }
            if (str_contains($lower, 'avatar_user_1') || str_contains($lower, 'user_1') || str_contains($lower, '-1') || str_contains($lower, '_1')) {
                return asset('images/sumut/avatar_user_1.webp');
            }
            if (str_contains($lower, 'avatar_user_2') || str_contains($lower, 'user_2') || str_contains($lower, '-2') || str_contains($lower, '_2')) {
                return asset('images/sumut/avatar_user_2.webp');
            }
            if (str_contains($lower, 'avatar_user_3') || str_contains($lower, 'user_3') || str_contains($lower, '-3') || str_contains($lower, '_3')) {
                return asset('images/sumut/avatar_user_3.webp');
            }
            if (str_contains($lower, 'avatar_user_4') || str_contains($lower, 'user_4') || str_contains($lower, '-4') || str_contains($lower, '_4')) {
                return asset('images/sumut/avatar_user_4.webp');
            }

            return asset('images/sumut/avatar_user_1.webp');
        }

        // Blog-specific fallback image overrides
        if (str_contains($lower, 'batak')) {
            return asset('images/sumut/batak_house.webp');
        }
        if (str_contains($lower, 'trekking') || str_contains($lower, 'orangutan')) {
            return asset('images/sumut/orangutan.webp');
        }
        if (str_contains($lower, 'spots') || str_contains($lower, 'photo')) {
            return asset('images/sumut/toba_landscape.webp');
        }
        if (str_contains($lower, 'best-time')) {
            return asset('images/sumut/toba_hero.webp');
        }
        if (str_contains($lower, 'transfer') || str_contains($lower, 'guide')) {
            return asset('images/sumut/sumatra_panorama.webp');
        }
        if (str_contains($lower, 'food') || str_contains($lower, 'kuliner')) {
            return asset('images/sumut/hotel_room.webp');
        }
        if (str_contains($lower, 'water') || str_contains($lower, 'sport')) {
            return asset('images/sumut/toba_hero.webp');
        }
        if (str_contains($lower, 'budget')) {
            return asset('images/sumut/toba_landscape.webp');
        }

        $clean = ltrim($path, '/');

        if (str_starts_with($clean, '_static/')) { $clean = substr($clean, 8); }
        // Dynamic fallback for non-existent local/storage files with premium Unsplash travel images
        $fileInPublic = public_path($clean);
        $cleanStoragePath = str_starts_with($clean, 'storage/') ? substr($clean, 8) : $clean;
        $fileInStorage = Storage::disk('public')->path($cleanStoragePath);

        if (! file_exists($fileInPublic) && ! file_exists($fileInStorage)) {
            return asset('images/home/tour.webp');
        }

        // Check if it's a static asset in the public folder (no storage/ prefix needed)
        // If file exists in public/assets or public/images, return asset() directly
        if (str_starts_with($clean, 'assets/') || str_starts_with($clean, 'images/')) {
            if (file_exists(public_path($clean))) {
                return asset($clean);
            }
        }

        // Strip redundant 'storage/' prefix before re-adding it
        if (str_starts_with($clean, 'storage/')) {
            $clean = substr($clean, strlen('storage/'));
        }

        // --- NEW: Smart WebP Prioritization ---
        // If it's a PNG/JPG/JPEG, check if a .webp version exists
        $extension = strtolower(pathinfo($clean, PATHINFO_EXTENSION));
        if (in_array($extension, ['png', 'jpg', 'jpeg'])) {
            $webpPath = preg_replace('/\.(png|jpg|jpeg)$/i', '.webp', $clean);
            if (Storage::disk('public')->exists($webpPath)) {
                return Storage::disk('public')->url($webpPath);
            }
        }
        // --------------------------------------

        // Paths that start with known media prefixes (from Media Library)
        foreach (['branding/', 'gallery/', 'cms/', 'packages/', 'blogs/', 'cities/'] as $prefix) {
            if (str_starts_with($clean, $prefix)) {
                return Storage::disk('public')->url($clean);
            }
        }

        // Final Fallback: Check if file exists in public/ directly, else assume storage
        if (file_exists(public_path($clean)) && ! is_dir(public_path($clean))) {
            return asset($clean);
        }

        return '/storage/' . ltrim($clean, '/');
        })();
    }
}

if (! function_exists('imageSrcset')) {
    /**
     * Build a responsive srcset from an ALREADY-RESOLVED image URL using the
     * "-400/-800" variant convention (public/images/sumut/foo.webp -> foo-400.webp, foo-800.webp).
     *
     * Pass the same URL you put in src="" (e.g. the output of imageUrl()/resolveImageUrl()).
     * Returns '' when the file or its variants don't exist — so storage uploads that use a
     * different variant scheme (mobile/medium/large) safely get no srcset instead of 404s.
     */
    function imageSrcset(?string $resolvedUrl): string
    {
        if (empty($resolvedUrl) || ! str_ends_with(strtolower($resolvedUrl), '.webp')) {
            return '';
        }

        // Dihitung sekali per URL per permintaan. Satu halaman grid memanggil
        // ini untuk setiap kartu, dan tiap panggilan menyentuh disk dua sampai
        // tiga kali; tanpa memo, halaman berisi 12 kartu membayar puluhan
        // pemeriksaan berkas untuk pertanyaan yang jawabannya sama.
        static $memo = [];
        if (array_key_exists($resolvedUrl, $memo)) {
            return $memo[$resolvedUrl];
        }

        $rel = ltrim((string) parse_url($resolvedUrl, PHP_URL_PATH), '/');
        if ($rel === '') {
            return $memo[$resolvedUrl] = '';
        }

        // ---- Jalur 1: aset bawaan di public/, konvensi "-400/-800".
        if (! preg_match('/-(400|800)\.webp$/i', $rel) && is_file(public_path($rel))) {
            $v400 = preg_replace('/\.webp$/i', '-400.webp', $rel);
            $v800 = preg_replace('/\.webp$/i', '-800.webp', $rel);

            $parts = [];
            if (is_file(public_path($v400))) {
                $parts[] = asset($v400).' 400w';
            }
            if (is_file(public_path($v800))) {
                $parts[] = asset($v800).' 800w';
            }
            if (! empty($parts)) {
                $parts[] = $resolvedUrl.' 1200w';

                return $memo[$resolvedUrl] = implode(', ', $parts);
            }
        }

        // ---- Jalur 2: unggahan admin di /storage/, konvensi mobile/medium/large.
        //
        // Dua konvensi hidup berdampingan di proyek ini: aset bawaan dipotong
        // dengan akhiran ukuran, sedangkan berkas yang diunggah lewat panel
        // admin dipotong ke dalam subfolder oleh pipeline Media. Kartu paket
        // memakai KEDUANYA -- paket contoh menunjuk gambar bawaan, paket asli
        // menunjuk unggahan admin -- jadi helper yang cuma tahu satu konvensi
        // akan diam persis di paket yang paling ramai dilihat.
        if (str_contains($rel, 'storage/')) {
            $bersih = substr($rel, strpos($rel, 'storage/') + 8);
            $dir = dirname($bersih);
            $base = basename($bersih);
            $awalan = ($dir === '.' || $dir === '/') ? '' : $dir.'/';

            $disk = \Illuminate\Support\Facades\Storage::disk('public');
            $parts = [];
            foreach (['mobile' => 480, 'medium' => 800, 'large' => 1200] as $folder => $lebar) {
                $jalur = $awalan.$folder.'/'.$base;
                if ($disk->exists($jalur)) {
                    $parts[] = $disk->url($jalur).' '.$lebar.'w';
                }
            }
            if (! empty($parts)) {
                return $memo[$resolvedUrl] = implode(', ', $parts);
            }
        }

        return $memo[$resolvedUrl] = '';
    }
}

if (! function_exists('dominantColor')) {
    /**
     * Get the dominant color hex code for an image path.
     * Uses static request-level cache for maximum performance.
     */
    function dominantColor(?string $path, string $default = '#e2e8f0'): string
    {
        if (empty($path) || $path === 'null') {
            return $default;
        }

        // Clean path to match database storage path
        $clean = ltrim($path, '/');
       if (str_starts_with($clean, '_static/')) { $clean = substr($clean, 8); }
         if (str_starts_with($clean, '_static/')) {
            $clean = substr($clean, 8);
        }
        if (str_starts_with($clean, 'storage/')) {
            $clean = substr($clean, strlen('storage/'));
        }

        static $colorCache = [];

        if (isset($colorCache[$clean])) {
            return $colorCache[$clean];
        }

        try {
            $media = Media::where('path', $clean)
                ->orWhere('path', $path)
                ->first();
            if ($media && $media->dominant_color) {
                $colorCache[$clean] = $media->dominant_color;

                return $media->dominant_color;
            }
        } catch (Exception $e) {
            // Database not ready or column missing
        }

        $colorCache[$clean] = $default;

        return $default;
    }
}

if (! function_exists('imageFallback')) {
    /**
     * Returns a local asset URL for onerror fallback attributes in img tags.
     * Safe to use inline in HTML onerror= attributes.
     */
    function imageFallback(?string $fallback = null): string
    {
        return $fallback ?? asset('images/home/tour.webp');
    }
}

if (! function_exists('formatPrice')) {
    /**
     * Convert and format price based on active locale/currency dynamic exchange rate.
     */
    function formatPrice($priceInIdr, $locale = null): string
    {
        return CurrencyHelper::formatPrice($priceInIdr, $locale);
    }
}

if (! function_exists('responsiveImage')) {
    /**
     * Render a responsive, lazy-loaded, SEO-optimized image tag with blur placeholder.
     */
    function responsiveImage(?string $path, string $class = '', string $alt = '', string $attributes = ''): string
    {
        $src = imageUrl($path);
        $srcsetAttr = '';
        $placeholderStyle = '';

        if (! empty($path) && $path !== 'null') {
            $clean = ltrim($path, '/');
            if (str_starts_with($clean, '_static/')) { $clean = substr($clean, 8); }
        if (str_starts_with($clean, 'storage/')) {
                $clean = substr($clean, strlen('storage/'));
            }

            try {
                $media = Media::where('path', $clean)
                    ->orWhere('path', $path)
                    ->first();

                if ($media) {
                    // Srcset resolution
                    $dir = dirname($media->path);
                    $base = basename($media->path);
                    $mobilePath = ($dir === '.' || $dir === '/') ? 'mobile/'.$base : $dir.'/mobile/'.$base;
                    $mediumPath = ($dir === '.' || $dir === '/') ? 'medium/'.$base : $dir.'/medium/'.$base;
                    $largePath = ($dir === '.' || $dir === '/') ? 'large/'.$base : $dir.'/large/'.$base;

                    $srcsetParts = [];
                    if (Storage::disk('public')->exists($mobilePath)) {
                        $srcsetParts[] = Storage::disk('public')->url($mobilePath).' 480w';
                    }
                    if (Storage::disk('public')->exists($mediumPath)) {
                        $srcsetParts[] = Storage::disk('public')->url($mediumPath).' 800w';
                    }
                    if (Storage::disk('public')->exists($largePath)) {
                        $srcsetParts[] = Storage::disk('public')->url($largePath).' 1200w';
                    }

                    if (! empty($srcsetParts)) {
                        $srcsetAttr = 'srcset="'.implode(', ', $srcsetParts).'"';
                    }

                    // Blur hash placeholder inline style
                    if ($media->blur_hash) {
                        $placeholderStyle = "background-image: url('{$media->blur_hash}'); background-size: cover; background-position: center; filter: blur(8px); transition: filter 0.5s ease-in-out, background-image 0.5s ease-in-out;";
                    }
                }
            } catch (Exception $e) {
            }
        }

        if (empty($alt)) {
            $alt = 'Wonderful Lake Toba Wisata';
        }

        return sprintf(
            '<img class="lazy-responsive-image %s" src="%s" %s sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw" alt="%s" style="%s" onload="this.style.filter=\'none\'; this.style.backgroundImage=\'none\';" %s>',
            e($class),
            e($src),
            $srcsetAttr,
            e($alt),
            $placeholderStyle,
            $attributes
        );
    }
}

if (! function_exists('ogBannerUrl')) {
    /**
     * Get the dynamic OpenGraph social share banner URL for a blog or package.
     */
    function ogBannerUrl($model = null): string
    {
        if (! $model) {
            return imageUrl(null);
        }

        $type = null;
        if ($model instanceof Package) {
            $type = 'package';
        } elseif ($model instanceof Blog) {
            $type = 'blog';
        }

        if ($type && isset($model->id)) {
            return route('og-banner', ['type' => $type, 'id' => $model->id]);
        }

        return imageUrl(null);
    }
}


