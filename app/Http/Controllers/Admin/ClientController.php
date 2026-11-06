<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Media;
use App\Traits\HandlesImageUploads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientController extends Controller
{
    use HandlesImageUploads;

    public function index()
    {
        $clients = Client::orderBy('sortOrder')->get();

        return view('admin.clients.index', compact('clients'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:15360',
            'logo_media_id' => 'nullable|exists:media,id',
            'websiteUrl' => 'nullable|url|max:500',
        ]);

        // Handle logo input (dual mode: file upload or media library)
        if ($request->hasFile('logo')) {
            // uploadAndIndex() returns a string path, not a Media model.
            $path = $this->uploadAndIndex($request->file('logo'), 'general', null, $validated['name'] . ' Logo');
            $mediaRecord = Media::where('path', $path)->latest()->first();
            $validated['logo_id'] = $mediaRecord?->id;
            // Keep legacy logo field for backwards compatibility
            $validated['logo'] = $path;
        } elseif ($request->filled('logo_media_id')) {
            $validated['logo_id'] = $request->logo_media_id;
            // Keep legacy logo field for backwards compatibility
            $media = Media::find($request->logo_media_id);
            $validated['logo'] = $media ? $media->path : null;
        }

        $validated['orderPriority'] = Client::max('orderPriority') + 1;

        Client::create($validated);

        return redirect()->back()->with('success', 'Client berhasil ditambahkan!');
    }

    public function destroy(Client $client)
    {
        if ($client->logo) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $client->logo));
        }
        $client->delete();

        return redirect()->back()->with('success', 'Client removed!');
    }
}
