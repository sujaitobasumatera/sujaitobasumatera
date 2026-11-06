<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Media;
use App\Models\Province;
use App\Models\Regency;
use App\Traits\HandlesImageUploads;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CityController extends Controller
{
    use HandlesImageUploads;

    public function index(Request $request)
    {
        $query = City::with('regency.province');

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->filled('province_id')) {
            $query->whereHas('regency', function ($q) use ($request) {
                $q->where('province_id', $request->province_id);
            });
        }

        if ($request->filled('regency_id')) {
            $query->where('regency_id', $request->regency_id);
        }

        $cities = $query->orderBy('name')->paginate(15);
        $provinces = Province::orderBy('name')->get();
        $regencies = $request->filled('province_id')
            ? Regency::where('province_id', $request->province_id)->orderBy('name')->get()
            : collect();

        // For the Categories Tab
        $all_regencies = Regency::with('province')->orderBy('name')->get();
        if ($request->filled('cat_province_id')) {
            $all_regencies = Regency::with('province')
                ->where('province_id', $request->cat_province_id)
                ->orderBy('name')
                ->get();
        }

        return view('admin.cities.index', compact('cities', 'provinces', 'regencies', 'all_regencies'));
    }

    public function create()
    {
        $provinces = Province::orderBy('name')->get();

        return view('admin.cities.create', compact('provinces'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'regency_id' => 'required',
            'regency_name_manual' => 'required_if:regency_id,manual|nullable|string|max:255',
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'city_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:15360',
            'city_image_media_id' => 'nullable|exists:media,id',
            'province_id' => 'required_if:regency_id,manual|nullable|exists:provinces,id',
        ]);

        if ($request->regency_id === 'manual') {
            $regency = Regency::firstOrCreate([
                'name' => $request->regency_name_manual,
                'province_id' => $request->province_id,
            ]);
            $validated['regency_id'] = $regency->id;
        }

        $validated['slug'] = Str::slug($validated['name']);

        // Handle image input (dual mode: file upload or media library)
        if ($request->hasFile('city_image')) {
            // uploadAndIndex() returns a string path, not a Media model.
            $path = $this->uploadAndIndex($request->file('city_image'), 'destinations', null, $validated['name']);
            $mediaRecord = Media::where('path', $path)->latest()->first();
            $validated['image_id'] = $mediaRecord?->id;
            // Keep legacy image field for backwards compatibility
            $validated['image'] = $path;
        } elseif ($request->filled('city_image_media_id')) {
            $validated['image_id'] = $request->city_image_media_id;
            // Keep legacy image field for backwards compatibility
            $media = Media::find($request->city_image_media_id);
            $validated['image'] = $media ? $media->path : null;
        }

        $regency = Regency::with('province')->find($validated['regency_id']);
        $validated['region'] = $regency->province->name;
        $validated['district'] = $regency->name;
        $validated['country'] = 'Indonesia';

        City::create($validated);

        return redirect()->route('admin.cities.index')
            ->with('success', 'Destinasi/Tempat berhasil ditambahkan!');
    }

    public function edit(City $city)
    {
        $provinces = Province::orderBy('name')->get();
        $regencies = Regency::where('province_id', $city->regency->province_id ?? 0)->orderBy('name')->get();

        return view('admin.cities.edit', compact('city', 'provinces', 'regencies'));
    }

    public function update(Request $request, City $city)
    {
        $validated = $request->validate([
            'regency_id' => 'required',
            'regency_name_manual' => 'required_if:regency_id,manual|nullable|string|max:255',
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'city_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:15360',
            'city_image_media_id' => 'nullable|exists:media,id',
            'province_id' => 'required_if:regency_id,manual|nullable|exists:provinces,id',
        ]);

        if ($request->regency_id === 'manual') {
            $regency = Regency::firstOrCreate([
                'name' => $request->regency_name_manual,
                'province_id' => $request->province_id,
            ]);
            $validated['regency_id'] = $regency->id;
        }

        // Image input (dual mode: file upload or media library) — must match the
        // field names posted by <x-image-input name="city_image"> in the edit form.
        if ($request->hasFile('city_image')) {
            $path = $this->uploadAndIndex($request->file('city_image'), 'destinations', null, $validated['name']);
            $mediaRecord = Media::where('path', $path)->latest()->first();
            $validated['image_id'] = $mediaRecord?->id;
            $validated['image'] = $path;
        } elseif ($request->filled('city_image_media_id')) {
            $validated['image_id'] = $request->city_image_media_id;
            $media = Media::find($request->city_image_media_id);
            $validated['image'] = $media ? $media->path : null;
        }

        if ($validated['name'] !== $city->name) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $regency = Regency::with('province')->find($validated['regency_id']);
        $validated['region'] = $regency->province->name;
        $validated['district'] = $regency->name;

        $city->update($validated);

        return redirect()->route('admin.cities.index')
            ->with('success', 'Data destinasi berhasil diperbarui!');
    }

    public function getRegencies(Request $request)
    {
        $regencies = Regency::where('province_id', $request->province_id)
            ->orderBy('name')
            ->get();

        return response()->json($regencies);
    }

    public function destroy(City $city)
    {
        $city->delete();

        return redirect()->route('admin.cities.index')
            ->with('success', 'Destinasi berhasil dihapus!');
    }
}
