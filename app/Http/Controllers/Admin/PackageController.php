<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Api\SyncController;
use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Package;
use App\Services\TourService;
use App\Traits\HandlesImageUploads;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PackageController extends Controller
{
    use HandlesImageUploads, LogsActivity;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $type = $request->get('type', 'tour'); // Default to tour if not specified
        $query = Package::with('city');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('locationTag', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by featured
        if ($request->filled('featured')) {
            $query->where('isFeatured', $request->featured === 'yes');
        }

        $packages = $query->orderBy('sortOrder')->orderBy('createdAt', 'desc')->paginate(15);

        return view('admin.packages.index', compact('packages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $cities = City::orderBy('name')->get();

        return view('admin.packages.create', compact('cities'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Package $package)
    {
        return view('admin.packages.show', compact('package'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Package $package)
    {
        $cities = City::orderBy('name')->get();

        return view('admin.packages.edit', compact('package', 'cities'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, TourService $tourService)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'shortDescription' => 'nullable|string',
            'description' => 'nullable|string',
            'locationTag' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'childPrice' => 'nullable|numeric|min:0',
            'duration' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive',
            'isFeatured' => 'boolean',
            'cityIds' => 'nullable|array',
            'cityIds.*' => 'exists:cities,id',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:15360',
            // Media tambahan. Nama input sengaja TIDAK 'videos'/'brochure':
            // keduanya kolom fillable, jadi berkas mentahnya akan ikut masuk
            // ke fill() dan menimpa kolom dengan objek UploadedFile.
            'mapEmbed' => 'nullable|string|max:2000',
            'video_links' => 'nullable|array',
            'video_links.*.src' => 'nullable|string|max:500',
            'video_links.*.title' => 'nullable|string|max:255',
            'video_links.*.gear' => 'nullable|string|max:255',
            'accommodations' => 'nullable|array',
            'accommodations.*.night' => 'nullable|integer|min:1|max:60',
            'accommodations.*.name' => 'nullable|string|max:255',
            'accommodations.*.class' => 'nullable|string|max:100',
            'accommodations.*.image' => 'nullable|string|max:500',
            // Bentuknya saja yang dijaga di sini. Yang menentukan tautan itu
            // benar-benar boleh dirender adalah Package::videoEmbedUrl(), dan
            // tautan tak dikenali dibuang di sana -- bukan diteruskan ke <iframe>.
            'accommodations.*.video' => 'nullable|string|max:500',
            'highlights' => 'nullable|array',
            'highlights.*.title' => 'nullable|string|max:255',
            'highlights.*.text' => 'nullable|string|max:1000',
            'accommodation_files' => 'nullable|array',
            'accommodation_files.*' => 'image|mimes:jpeg,png,jpg,webp|max:15360',
            'video_files' => 'nullable|array',
            'video_files.*' => 'file|mimetypes:video/mp4,video/webm,video/quicktime|max:'.maxUploadKb(51200),
            'brochure_file' => 'nullable|file|mimes:pdf|max:'.maxUploadKb(20480),
            'price_image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:15360',
            'remove_videos' => 'nullable|array',
            'remove_brochure' => 'nullable|boolean',
            'remove_price_image' => 'nullable|boolean',
            'itinerary' => 'nullable|array',
            'cost_price' => 'nullable|numeric|min:0',
            'includes' => 'nullable|array',
            'excludes' => 'nullable|array',
            'media_ids' => 'nullable|array',
            'pricingDetails' => 'nullable|array',
            'pricingDetails.additional_services' => 'nullable|array',
            'pricingDetails.additional_services.*.name' => 'required|string|max:255',
            'pricingDetails.additional_services.*.icon' => 'required|string|max:255',
            'pricingDetails.additional_services.*.price' => 'required|numeric|min:0',
            // Kendaraan besar: tarif PENGGANTI per pax, bukan biaya tambahan.
            'pricingDetails.vehicle' => 'nullable|array',
            'pricingDetails.vehicle.name' => 'nullable|string|max:255',
            'pricingDetails.vehicle.min_pax' => 'nullable|integer|min:1',
            'pricingDetails.vehicle.price' => 'nullable|numeric|min:0',
            'pricingDetails.tiers' => 'nullable|array',
            'pricingDetails.tiers.*.min_pax' => 'required|integer|min:1',
            'pricingDetails.tiers.*.max_pax' => 'required|integer|gte:pricingDetails.tiers.*.min_pax',
            'pricingDetails.tiers.*.price' => 'required|numeric|min:0',
            // Wajib. Begitu sebuah paket punya harga grosir, harga anak TIDAK
            // lagi boleh diam-diam diambil dari harga anak paket -- itu
            // mencampur harga dasar dengan harga tier, dan tamu rombongan
            // membayar harga anak yang tidak pernah diniatkan siapa pun.
            'pricingDetails.tiers.*.child_price' => 'required|numeric|min:0',
        ]);

        $pricingDetails = $request->input('pricingDetails', []);
        if (!isset($pricingDetails['additional_services'])) {
            $pricingDetails['additional_services'] = [];
        }
        // Tarif kosong = paket ini tidak menawarkan kendaraan besar. Blok
        // setengah terisi dibuang, bukan disimpan: `vehicle` tanpa harga akan
        // memunculkan pilihan kendaraan seharga nol di halaman pemesanan.
        $kendaraan = $pricingDetails['vehicle'] ?? null;
        if (! is_array($kendaraan) || trim((string) ($kendaraan['price'] ?? '')) === '') {
            unset($pricingDetails['vehicle']);
        } else {
            $pricingDetails['vehicle'] = [
                'name' => trim((string) ($kendaraan['name'] ?? '')) ?: 'Van',
                'min_pax' => (int) ($kendaraan['min_pax'] ?? 6) ?: 6,
                'price' => (float) $kendaraan['price'],
            ];
        }
        if (!isset($pricingDetails['tiers'])) {
            $pricingDetails['tiers'] = [];
        }
        $validated['pricingDetails'] = $pricingDetails;

        try {
            $validated['image_files'] = $request->file('images');
            $validated['video_files'] = $request->file('video_files');
            $validated['brochure_file'] = $request->file('brochure_file');
            $validated['price_image_file'] = $request->file('price_image_file');
            // Dikirim eksplisit walau kosong. Kalau kuncinya hilang saat admin
            // menghapus baris tautan terakhir, service tidak punya cara
            // membedakan "tidak ada perubahan" dari "hapus semuanya".
            $validated['video_links'] = $request->input('video_links', []);
            $validated['remove_videos'] = $request->input('remove_videos', []);
            $validated['accommodations'] = $request->input('accommodations', []);
            $validated['highlights'] = $request->input('highlights', []);
            $validated['accommodation_files'] = $request->file('accommodation_files', []);
            $package = $tourService->savePackage($validated);

            $this->logActivity('created', "Created new package: {$package->name}", $package);
            SyncController::triggerSync();

            return redirect()->route('admin.packages.index')->with('success', 'Package created successfully!');
        } catch (\Exception $e) {
            Log::error('Package Creation Failed: '.$e->getMessage());

            return back()->withInput()->with('error', 'Failed to create package. '.$e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Package $package, TourService $tourService)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'shortDescription' => 'nullable|string',
            'description' => 'nullable|string',
            'locationTag' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'childPrice' => 'nullable|numeric|min:0',
            'duration' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive',
            'isFeatured' => 'boolean',
            'cityIds' => 'nullable|array',
            'cityIds.*' => 'exists:cities,id',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:15360',
            'remove_images' => 'nullable|array',
            // Media tambahan. Nama input sengaja TIDAK 'videos'/'brochure':
            // keduanya kolom fillable, jadi berkas mentahnya akan ikut masuk
            // ke fill() dan menimpa kolom dengan objek UploadedFile.
            'mapEmbed' => 'nullable|string|max:2000',
            'video_links' => 'nullable|array',
            'video_links.*.src' => 'nullable|string|max:500',
            'video_links.*.title' => 'nullable|string|max:255',
            'video_links.*.gear' => 'nullable|string|max:255',
            'accommodations' => 'nullable|array',
            'accommodations.*.night' => 'nullable|integer|min:1|max:60',
            'accommodations.*.name' => 'nullable|string|max:255',
            'accommodations.*.class' => 'nullable|string|max:100',
            'accommodations.*.image' => 'nullable|string|max:500',
            // Bentuknya saja yang dijaga di sini. Yang menentukan tautan itu
            // benar-benar boleh dirender adalah Package::videoEmbedUrl(), dan
            // tautan tak dikenali dibuang di sana -- bukan diteruskan ke <iframe>.
            'accommodations.*.video' => 'nullable|string|max:500',
            'highlights' => 'nullable|array',
            'highlights.*.title' => 'nullable|string|max:255',
            'highlights.*.text' => 'nullable|string|max:1000',
            'accommodation_files' => 'nullable|array',
            'accommodation_files.*' => 'image|mimes:jpeg,png,jpg,webp|max:15360',
            'video_files' => 'nullable|array',
            'video_files.*' => 'file|mimetypes:video/mp4,video/webm,video/quicktime|max:'.maxUploadKb(51200),
            'brochure_file' => 'nullable|file|mimes:pdf|max:'.maxUploadKb(20480),
            'price_image_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:15360',
            'remove_videos' => 'nullable|array',
            'remove_brochure' => 'nullable|boolean',
            'remove_price_image' => 'nullable|boolean',
            'itinerary' => 'nullable|array',
            'cost_price' => 'nullable|numeric|min:0',
            'includes' => 'nullable|array',
            'excludes' => 'nullable|array',
            'media_ids' => 'nullable|array',
            'pricingDetails' => 'nullable|array',
            'pricingDetails.additional_services' => 'nullable|array',
            'pricingDetails.additional_services.*.name' => 'required|string|max:255',
            'pricingDetails.additional_services.*.icon' => 'required|string|max:255',
            'pricingDetails.additional_services.*.price' => 'required|numeric|min:0',
            // Kendaraan besar: tarif PENGGANTI per pax, bukan biaya tambahan.
            'pricingDetails.vehicle' => 'nullable|array',
            'pricingDetails.vehicle.name' => 'nullable|string|max:255',
            'pricingDetails.vehicle.min_pax' => 'nullable|integer|min:1',
            'pricingDetails.vehicle.price' => 'nullable|numeric|min:0',
            'pricingDetails.tiers' => 'nullable|array',
            'pricingDetails.tiers.*.min_pax' => 'required|integer|min:1',
            'pricingDetails.tiers.*.max_pax' => 'required|integer|gte:pricingDetails.tiers.*.min_pax',
            'pricingDetails.tiers.*.price' => 'required|numeric|min:0',
            // Wajib. Begitu sebuah paket punya harga grosir, harga anak TIDAK
            // lagi boleh diam-diam diambil dari harga anak paket -- itu
            // mencampur harga dasar dengan harga tier, dan tamu rombongan
            // membayar harga anak yang tidak pernah diniatkan siapa pun.
            'pricingDetails.tiers.*.child_price' => 'required|numeric|min:0',
        ]);

        $pricingDetails = $request->input('pricingDetails', []);
        if (!isset($pricingDetails['additional_services'])) {
            $pricingDetails['additional_services'] = [];
        }
        // Tarif kosong = paket ini tidak menawarkan kendaraan besar. Blok
        // setengah terisi dibuang, bukan disimpan: `vehicle` tanpa harga akan
        // memunculkan pilihan kendaraan seharga nol di halaman pemesanan.
        $kendaraan = $pricingDetails['vehicle'] ?? null;
        if (! is_array($kendaraan) || trim((string) ($kendaraan['price'] ?? '')) === '') {
            unset($pricingDetails['vehicle']);
        } else {
            $pricingDetails['vehicle'] = [
                'name' => trim((string) ($kendaraan['name'] ?? '')) ?: 'Van',
                'min_pax' => (int) ($kendaraan['min_pax'] ?? 6) ?: 6,
                'price' => (float) $kendaraan['price'],
            ];
        }
        if (!isset($pricingDetails['tiers'])) {
            $pricingDetails['tiers'] = [];
        }
        $validated['pricingDetails'] = $pricingDetails;

        try {
            $validated['image_files'] = $request->file('images');
            $validated['video_files'] = $request->file('video_files');
            $validated['brochure_file'] = $request->file('brochure_file');
            $validated['price_image_file'] = $request->file('price_image_file');
            // Dikirim eksplisit walau kosong. Kalau kuncinya hilang saat admin
            // menghapus baris tautan terakhir, service tidak punya cara
            // membedakan "tidak ada perubahan" dari "hapus semuanya".
            $validated['video_links'] = $request->input('video_links', []);
            $validated['remove_videos'] = $request->input('remove_videos', []);
            $validated['accommodations'] = $request->input('accommodations', []);
            $validated['highlights'] = $request->input('highlights', []);
            $validated['accommodation_files'] = $request->file('accommodation_files', []);
            $tourService->savePackage($validated, $package);

            $this->logActivity('updated', "Updated package: {$package->name}", $package);
            SyncController::triggerSync();

            return redirect()->route('admin.packages.index')->with('success', 'Package updated successfully!');
        } catch (\Exception $e) {
            Log::error('Package Update Failed: '.$e->getMessage());

            return back()->withInput()->with('error', 'Failed to update package. '.$e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Package $package, TourService $tourService)
    {
        $name = $package->name;
        $tourService->deletePackage($package);

        $this->logActivity('deleted', "Deleted package: {$name}");
        SyncController::triggerSync();

        return redirect()->route('admin.packages.index')->with('success', 'Package deleted successfully!');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['message' => 'No IDs provided'], 400);
        }

        Package::whereIn('id', $ids)->delete();
        $this->logActivity('bulk_deleted', 'Bulk deleted '.count($ids).' packages');

        return response()->json(['message' => 'Packages deleted successfully']);
    }

    public function export(Request $request)
    {
        $format = $request->get('format', 'xlsx');
        $filename = 'packages-export-'.date('Y-m-d').'.'.$format;

        $query = Package::query();
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $packages = $query->get();

        return \Excel::download(new class($packages) implements FromCollection, WithHeadings, WithMapping
        {
            protected $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function collection()
            {
                return $this->data;
            }

            public function headings(): array
            {
                return ['ID', 'Nama', 'Lokasi', 'Harga', 'Durasi', 'Status', 'Featured', 'Dibuat Pada'];
            }

            public function map($row): array
            {
                return [
                    $row->id,
                    $row->name,
                    $row->locationTag,
                    $row->price,
                    $row->duration,
                    strtoupper($row->status),
                    $row->isFeatured ? 'Ya' : 'Tidak',
                    $row->createdAt->format('Y-m-d H:i'),
                ];
            }
        }, $filename);
    }

    public function restore($id)
    {
        $package = Package::onlyTrashed()->findOrFail($id);
        $package->restore();

        $this->logActivity('restored', "Restored package: {$package->name}", $package);

        return redirect()->route('admin.packages.index')
            ->with('success', 'Package restored successfully!');
    }

    public function toggleStatus(Package $package)
    {
        $package->status = ($package->status === 'active') ? 'inactive' : 'active';
        $package->save();

        $this->logActivity('toggled', "Toggled status of package: {$package->name} → {$package->status}", $package);
        SyncController::triggerSync();

        return response()->json([
            'success' => true,
            'status' => $package->status,
            'message' => 'Status berhasil diubah ke '.strtoupper($package->status),
        ]);
    }

    public function duplicate(Package $package)
    {
        try {
            $newPackage = $package->replicate();
            $newPackage->name = $package->name . ' (Copy)';
            $newPackage->slug = \Illuminate\Support\Str::slug($newPackage->name) . '-' . time();
            $newPackage->status = 'inactive'; // Default duplicated packages to inactive
            $newPackage->save();

            // Replicate images
            foreach ($package->packageImages as $image) {
                $newPackage->packageImages()->create([
                    'image_path' => $image->image_path,
                    'sort_order' => $image->sort_order,
                ]);
            }

            // Replicate cities
            $newPackage->cities()->sync($package->cities->pluck('id')->toArray());

            $this->logActivity('duplicated', "Duplicated package: {$package->name} to {$newPackage->name}", $newPackage);
            SyncController::triggerSync();

            return redirect()->route('admin.packages.edit', $newPackage->id)
                ->with('success', 'Package successfully duplicated! You are now editing the copy.');
        } catch (\Exception $e) {
            Log::error('Package Duplication Failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to duplicate package. ' . $e->getMessage());
        }
    }
}
