<?php

namespace App\Services;

use App\Models\Blog;
use App\Models\City;
use App\Models\GalleryImage;
use App\Models\Media;
use App\Models\Package;
use App\Models\Setting;
use App\Traits\HandlesImageUploads;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TourService
{
    use HandlesImageUploads;

    /**
     * Create or update a tour package with comprehensive image handling.
     */
    public function savePackage(array $data, ?Package $package = null)
    {
        $package = $package ?? new Package;

        // 1. Prepare Data
        if (! $package->exists) {
            $data['slug'] = Str::slug($data['name']);
        } elseif (isset($data['name']) && $data['name'] !== $package->name) {
            $data['slug'] = Str::slug($data['name']);
        }

        // 2. Sanitize Includes/Excludes
        $data['includes'] = array_values(array_filter($data['includes'] ?? [], fn ($v) => ! empty(trim((string) $v))));
        $data['excludes'] = array_values(array_filter($data['excludes'] ?? [], fn ($v) => ! empty(trim((string) $v))));

        // 3. Handle Image Removals (for update)
        $currentImages = $package->images ?? [];
        if (isset($data['remove_images']) && is_array($data['remove_images'])) {
            foreach ($data['remove_images'] as $imgToRemove) {
                if (($key = array_search($imgToRemove, $currentImages)) !== false) {
                    unset($currentImages[$key]);
                    Storage::disk('public')->delete($imgToRemove);
                }
            }
        }

        // 4. Handle New File Uploads
        if (isset($data['image_files']) && is_array($data['image_files'])) {
            foreach ($data['image_files'] as $file) {
                $path = $this->uploadAndIndex($file, 'packages', 'packages', $data['name']);
                if ($path) {
                    $currentImages[] = $path;
                }
            }
        }

        // 5. Handle Media Library IDs
        if (isset($data['media_ids']) && is_array($data['media_ids'])) {
            $mediaPaths = Media::whereIn('id', $data['media_ids'])->pluck('path')->toArray();
            $currentImages = array_merge($currentImages, $mediaPaths);
        }

        $data['images'] = array_values(array_unique($currentImages));

        // 5b. Video: baris tautan dari form + berkas unggahan digabung jadi
        // satu daftar. Kuncinya `videos` hanya ditulis kalau formnya memang
        // mengirim salah satunya -- kalau tidak, kolomnya jangan disentuh,
        // supaya simpan dari form lain (mis. toggle status) tidak mengosongkan
        // video yang sudah ada.
        if (array_key_exists('video_links', $data) || isset($data['video_files']) || isset($data['remove_videos'])) {
            $keep = [];
            $remove = array_filter((array) ($data['remove_videos'] ?? []));

            foreach ((array) ($package->videos ?? []) as $existing) {
                if (! is_array($existing)) {
                    continue;
                }
                $src = (string) ($existing['src'] ?? '');
                if ($src === '' || in_array($src, $remove, true)) {
                    // Berkas yang dibuang ikut dihapus dari disk; kalau tidak,
                    // video 40 MB itu menghuni hosting selamanya tanpa rujukan.
                    if ($src !== '' && ($existing['type'] ?? '') === 'file') {
                        Storage::disk('public')->delete($src);
                    }

                    continue;
                }
                $keep[] = $existing;
            }

            // Baris tautan dikirim ulang utuh setiap simpan, jadi yang lama
            // dibuang dan diganti isi form -- itu yang membuat tombol hapus
            // pada baris tautan benar-benar menghapus.
            $keep = array_values(array_filter($keep, fn ($v) => ($v['type'] ?? 'link') === 'file'));

            foreach ((array) ($data['video_links'] ?? []) as $row) {
                $src = trim((string) (is_array($row) ? ($row['src'] ?? '') : $row));
                if ($src === '') {
                    continue;
                }
                $keep[] = [
                    'type' => 'link',
                    'src' => $src,
                    'title' => trim((string) (is_array($row) ? ($row['title'] ?? '') : '')),
                    'gear' => trim((string) (is_array($row) ? ($row['gear'] ?? '') : '')),
                ];
            }

            foreach ((array) ($data['video_files'] ?? []) as $file) {
                if (! $file) {
                    continue;
                }
                // Video TIDAK lewat uploadAndIndex: helper itu mengonversi ke
                // WebP dan mengindeks ke pustaka gambar.
                $path = $file->store('packages/videos', 'public');
                if ($path) {
                    $keep[] = ['type' => 'file', 'src' => $path, 'title' => $file->getClientOriginalName()];
                }
            }

            $data['videos'] = array_values($keep);
        }

        // 5b-2. Penginapan per malam. Barisnya dikirim ulang utuh tiap simpan,
        // foto lama dipertahankan lewat field tersembunyi kecuali ada unggahan
        // baru di baris yang sama.
        if (array_key_exists('accommodations', $data)) {
            $rows = [];
            $files = (array) ($data['accommodation_files'] ?? []);

            foreach ((array) $data['accommodations'] as $i => $row) {
                if (! is_array($row)) {
                    continue;
                }

                $name = trim((string) ($row['name'] ?? ''));
                if ($name === '') {
                    continue;
                }

                $image = trim((string) ($row['image'] ?? ''));
                if (! empty($files[$i])) {
                    $baru = $this->uploadAndIndex($files[$i], 'packages', 'akomodasi', $name);
                    if ($baru) {
                        // Foto lama baris ini tidak lagi dirujuk siapa pun.
                        if ($image !== '') {
                            Storage::disk('public')->delete($image);
                        }
                        $image = $baru;
                    }
                }

                $rows[] = [
                    'night' => (int) ($row['night'] ?? 0) ?: count($rows) + 1,
                    'name' => $name,
                    'class' => trim((string) ($row['class'] ?? '')),
                    'image' => $image,
                ];
            }

            $data['accommodations'] = $rows;
        }

        // 5b-3. Pembeda khusus paket. Sama seperti penginapan: barisnya dikirim
        // ulang utuh tiap simpan, jadi menghapus baris di form = menghapus
        // poinnya. Baris tanpa judul dibuang di sini, bukan disaring saat
        // render -- supaya yang tersimpan di database sudah bersih dan tidak
        // ada pembaca lain yang perlu tahu aturannya.
        if (array_key_exists('highlights', $data)) {
            $rows = [];

            foreach ((array) $data['highlights'] as $row) {
                if (! is_array($row)) {
                    continue;
                }

                $title = trim((string) ($row['title'] ?? ''));
                if ($title === '') {
                    continue;
                }

                $rows[] = [
                    'title' => $title,
                    'text' => trim((string) ($row['text'] ?? '')),
                ];
            }

            $data['highlights'] = $rows;
        }

        // 5c. Brosur PDF -- satu berkas, unggahan baru menggantikan yang lama.
        if (! empty($data['remove_brochure']) && $package->brochure) {
            Storage::disk('public')->delete($package->brochure);
            $data['brochure'] = null;
        }
        if (isset($data['brochure_file']) && $data['brochure_file']) {
            if ($package->brochure) {
                Storage::disk('public')->delete($package->brochure);
            }
            $data['brochure'] = $data['brochure_file']->store('packages/brochures', 'public');
        }

        // 5d. Gambar informasi harga -- pola sama dengan brosur di atas:
        // satu berkas, unggahan baru menggantikan yang lama.
        if (! empty($data['remove_price_image']) && $package->priceImage) {
            Storage::disk('public')->delete($package->priceImage);
            $data['priceImage'] = null;
        }
        if (isset($data['price_image_file']) && $data['price_image_file']) {
            if ($package->priceImage) {
                Storage::disk('public')->delete($package->priceImage);
            }
            $data['priceImage'] = $data['price_image_file']->store('packages/price-images', 'public');
        }

        // 6. Save Package
        $package->fill($data);
        $package->save();

        // 7. Sync Relational PackageImage table
        $package->packageImages()->delete();
        foreach ($data['images'] as $index => $imgPath) {
            $package->packageImages()->create([
                'image_path' => $imgPath,
                'sort_order' => $index,
            ]);
        }

        // 8. Sync Cities
        if (isset($data['cityIds']) && is_array($data['cityIds'])) {
            $package->cities()->sync($data['cityIds']);
        } elseif (isset($data['cityIds']) && empty($data['cityIds'])) {
            $package->cities()->detach();
        }

        return $package;
    }

    /**
     * Soft-delete a package. Physical image files are intentionally kept so a
     * subsequent restore() still has its images; orphaned files are reclaimed
     * by the media audit clean-orphans command after permanent deletion.
     */
    public function deletePackage(Package $package)
    {
        return $package->delete();
    }



    /**
     * Clear tour related cache.
     */
    public function clearCache($slug = null)
    {
        Cache::forget('tour_packages_all');
        Cache::forget('tour_packages_nav');
        Cache::forget('featured_packages');
        Cache::forget('tour_blogs_all');
        Cache::forget('tour_homepage_data');
        Cache::forget('tour_gallery_all');
        Cache::forget('site_settings_structured_cms_tour_general');

        if ($slug) {
            Cache::forget("package_detail_{$slug}");
        }

        Log::info('Cache cleared for '.($slug ?? 'all packages'));

        return true;
    }

    /**
     * Get CMS Tour Settings.
     */
    public function getTourSettings()
    {
        return Setting::where('key', 'cms_tour')->first()?->value ?? [];
    }

    /**
     * Get featured active tour packages. Cached; invalidated via clearCache().
     */
    public function getFeaturedPackages()
    {
        return Cache::remember('featured_packages', 600, function () {
            $featured = Package::where('status', 'active')
                ->where('isFeatured', true)
                ->with(['packageImages', 'city'])
                ->orderBy('sortOrder')
                ->get();

            // Fallback: if no featured packages, show all active ones
            if ($featured->isEmpty()) {
                return Package::where('status', 'active')
                    ->with(['packageImages', 'city'])
                    ->orderBy('sortOrder')
                    ->get();
            }

            return $featured;
        });
    }

    /**
     * Get tour blog posts. Does NOT filter by category so all published posts appear.
     * Eager-loads coverImage (prevents N+1 on the appended image_url) and caches
     * the full list; the optional limit is applied to the cached collection.
     */
    public function getBlogs($limit = null)
    {
        $blogs = Cache::remember('tour_blogs_all', 600, function () {
            return Blog::where('status', 'published')
                ->with('coverImage')
                ->latest('createdAt')
                ->get();
        });

        return $limit ? $blogs->take($limit) : $blogs;
    }

    /**
     * Get all active tour packages with eager loaded images and city. Cached.
     *
     * locationTag ikut disertakan agar filter Alpine di /tour/packages bisa
     * mencocokkan nama destinasi (mis. "Samosir", "Berastagi") yang tidak selalu
     * muncul di nama paket — sebelumnya ketik "Samosir" bisa menghasilkan 0 hasil.
     */
    public function getAllPackages()
    {
        return Cache::remember('tour_packages_all', 600, function () {
            return Package::where('status', 'active')
                ->with(['packageImages', 'city', 'cities'])
                ->orderBy('sortOrder')
                ->get();
        });
    }

    /**
     * Slim package list for the navbar dropdown. Hanya kolom yang dirender menu,
     * supaya murah dipanggil di setiap halaman.
     */
    public function getNavPackages()
    {
        return Cache::remember('tour_packages_nav', 600, function () {
            return Package::where('status', 'active')
                ->orderBy('sortOrder')
                ->get(['id', 'slug', 'name', 'duration', 'translations']);
        });
    }

    /**
     * Get all cities.
     */
    public function getCities()
    {
        return City::orderBy('name')->get();
    }

    /**
     * Get active gallery images for Tour.
     */
    public function getGallery()
    {
        // Dulu dibatasi ke kategori 'tour'/'Tour'/kosong saja. Akibatnya foto
        // berkategori Adventure, Culture, Waterfall, dan Wildlife — yang semuanya
        // foto tour juga — tidak pernah tampil: separuh isi galeri hilang diam-diam.
        // Sekaligus itu membuat chip filter di halaman galeri mustahil punya lebih
        // dari satu pilihan, jadi filternya tampak rusak padahal kueri ini
        // penyebabnya. Galeri publik = semua gambar yang admin tandai aktif.
        // Di-cache seperti data tour lain; key 'tour_gallery_all' ikut dibersihkan
        // clearCache() saat admin mengubah galeri (via Syncable → triggerSync).
        return Cache::remember('tour_gallery_all', 600, function () {
            return GalleryImage::where('isActive', true)
                ->with('imageMedia')
                ->orderBy('orderPriority')
                ->get();
        });
    }

    /**
     * Get active package by slug.
     */
    public function getPackageBySlug($slug)
    {
        return Cache::remember("package_detail_{$slug}", 600, function () use ($slug) {
            return Package::where('slug', $slug)
                ->where('status', 'active')
                ->with(['packageImages', 'city', 'cities'])
                ->first();
        });
    }

    /**
     * Get active blog post by slug.
     */
    public function getBlogPost($slug)
    {
        return Blog::where('slug', $slug)
            ->where('status', 'published')
            ->first();
    }

    /**
     * Get related blog posts (any category).
     */
    public function getRelatedBlogs($currentId, $limit = 3)
    {
        return Blog::where('status', 'published')
            ->where('id', '!=', $currentId)
            ->with('coverImage')
            ->latest('createdAt')
            ->limit($limit)
            ->get();
    }
}
