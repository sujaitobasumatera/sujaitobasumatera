{{-- Media tambahan paket: video (tautan & unggahan), peta, brosur PDF.
     Satu partial dipakai form create DAN edit -- dua salinan pasti melenceng,
     dan yang ketinggalan biasanya form create yang jarang dibuka.
     Butuh $package (boleh null saat create). --}}
@php
    $pkgVideos = collect((array) ($package->videos ?? []))->filter(fn ($v) => is_array($v) && ! empty($v['src']));
    $pkgVideoLinks = $pkgVideos->filter(fn ($v) => ($v['type'] ?? 'link') !== 'file')->values();
    $pkgVideoFiles = $pkgVideos->filter(fn ($v) => ($v['type'] ?? 'link') === 'file')->values();

    // Baris tautan yang dikirim ulang ke form: pakai input lama kalau
    // validasi memantulkan halaman ini, kalau tidak pakai isi database.
    $oldLinks = old('video_links');
    $linkRows = is_array($oldLinks)
        ? array_values(array_filter($oldLinks, fn ($r) => is_array($r)))
        : $pkgVideoLinks->map(fn ($v) => [
            'src' => $v['src'] ?? '',
            'title' => $v['title'] ?? '',
            'gear' => $v['gear'] ?? '',
        ])->all();

    $oldAkomodasi = old('accommodations');
    $akomodasiRows = is_array($oldAkomodasi)
        ? array_values(array_filter($oldAkomodasi, fn ($r) => is_array($r)))
        : ($package?->accommodationList() ?? []);

    $oldHighlights = old('highlights');
    $highlightRows = is_array($oldHighlights)
        ? array_values(array_filter($oldHighlights, fn ($r) => is_array($r)))
        : ($package?->highlightList() ?? []);
@endphp

<div class="border-t border-gray-200 pt-6 mt-2 space-y-6">
    <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-slate-900 text-white flex items-center justify-center shrink-0">
            <i class="fas fa-photo-film text-sm"></i>
        </div>
        <div>
            <h3 class="text-sm font-black text-slate-800 tracking-tight leading-none">Media Tambahan</h3>
            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">Video, peta lokasi & brosur</p>
        </div>
    </div>

    {{-- ============ VIDEO ============ --}}
    <div x-data="{ links: @js($linkRows) }">
        <label class="block text-sm font-bold text-gray-700 mb-2">Video Paket</label>

        {{-- Baris tautan YouTube/Vimeo. Dikirim ulang utuh setiap simpan,
             jadi menghapus baris di sini = menghapus videonya. --}}
        <template x-for="(row, idx) in links" :key="'vlink' + idx">
            <div class="flex flex-col sm:flex-row gap-2 mb-2">
                <input type="url" :name="'video_links[' + idx + '][src]'" x-model="row.src"
                       placeholder="https://www.youtube.com/watch?v=..."
                       class="flex-1 px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-toba-green focus:border-transparent transition">
                <input type="text" :name="'video_links[' + idx + '][title]'" x-model="row.title"
                       placeholder="Judul (opsional)"
                       class="sm:w-44 px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-toba-green focus:border-transparent transition">
                {{-- Alat rekam: ini yang mengubah video dari pemanis jadi bukti
                     bahwa rekamannya milik tim sendiri, bukan stok. --}}
                <input type="text" :name="'video_links[' + idx + '][gear]'" x-model="row.gear"
                       placeholder="Alat (mis. DJI Mavic 3)"
                       class="sm:w-44 px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-toba-green focus:border-transparent transition">
                <button type="button" @click="links.splice(idx, 1)"
                        class="shrink-0 w-10 h-10 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white flex items-center justify-center transition">
                    <i class="fas fa-trash-alt text-xs"></i>
                </button>
            </div>
        </template>

        <button type="button" @click="links.push({ src: '', title: '', gear: '' })"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-900 hover:text-white text-slate-700 text-[10px] font-black uppercase tracking-widest transition">
            <i class="fas fa-plus text-[10px]"></i> Tambah Tautan Video
        </button>
        <p class="text-[11px] text-gray-400 mt-2 italic">* YouTube, YouTube Shorts, atau Vimeo. Tautan selain itu diabaikan saat ditampilkan.</p>

        {{-- Video yang sudah diunggah ke server --}}
        @if($pkgVideoFiles->count())
            <div class="mt-5 space-y-2">
                <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest">Video Terunggah</label>
                @foreach($pkgVideoFiles as $vid)
                    <label class="flex items-center gap-3 px-4 py-3 rounded-xl border border-gray-200 hover:border-rose-300 cursor-pointer transition group">
                        <input type="checkbox" name="remove_videos[]" value="{{ $vid['src'] }}"
                               class="w-4 h-4 rounded border-gray-300 text-rose-600 focus:ring-rose-500">
                        <i class="fas fa-file-video text-slate-400 group-hover:text-rose-500 transition"></i>
                        <span class="text-xs text-slate-600 truncate flex-1">{{ $vid['title'] ?: basename($vid['src']) }}</span>
                        <span class="text-[9px] font-black uppercase tracking-widest text-rose-500">Centang untuk hapus</span>
                    </label>
                @endforeach
            </div>
        @endif

        <div class="mt-4 flex flex-col gap-2">
            {{-- Angkanya dari ini_get() server, bukan angka harapan: PHP
                 menolak berkas kelewat besar sebelum Laravel sempat memberi
                 pesan, jadi tulisan di sini harus jujur. --}}
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest">Atau Unggah Berkas Video (MP4 / WebM / MOV, maks {{ round(maxUploadKb(51200) / 1024) }} MB)</label>
            <input type="file" name="video_files[]" multiple accept="video/mp4,video/webm,video/quicktime"
                   class="block w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:uppercase file:tracking-widest file:bg-slate-900 file:text-white hover:file:bg-slate-800 transition cursor-pointer">
            <p class="text-[11px] text-gray-400 italic">* Berkas video memakai kuota penyimpanan & bandwidth hosting setiap kali diputar. Untuk video panjang, tautan YouTube jauh lebih murah.</p>
        </div>
    </div>

    {{-- ============ PENGINAPAN PER MALAM ============ --}}
    <div x-data="{ nights: @js($akomodasiRows) }">
        <label class="block text-sm font-bold text-gray-700 mb-1">Penginapan per Malam</label>
        <p class="text-[11px] text-gray-500 mb-3 flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1 font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-md"><i class="fas fa-ruler-combined text-toba-green"></i> Rekomendasi Foto Hotel: 800 &times; 600 px (4:3) atau 600 &times; 600 px (1:1)</span>
            <span class="text-gray-400 italic">• Tampil di halaman detail tanpa form. Baris tanpa nama hotel diabaikan.</span>
        </p>

        <template x-for="(row, idx) in nights" :key="'night' + idx">
            <div class="flex flex-col sm:flex-row gap-2 mb-2 items-start">
                <div class="flex items-center gap-2 shrink-0">
                    <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">Malam</span>
                    <input type="number" min="1" max="60" :name="'accommodations[' + idx + '][night]'" x-model="row.night"
                           class="w-16 px-3 py-2.5 border border-gray-300 rounded-xl text-sm text-center focus:ring-2 focus:ring-toba-green focus:border-transparent transition">
                </div>
                <input type="text" :name="'accommodations[' + idx + '][name]'" x-model="row.name"
                       placeholder="Nama hotel / penginapan"
                       class="flex-1 px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-toba-green focus:border-transparent transition">
                <input type="text" :name="'accommodations[' + idx + '][class]'" x-model="row.class"
                       placeholder="Kelas (mis. Bintang 4)"
                       class="sm:w-44 px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-toba-green focus:border-transparent transition">
                {{-- Video penginapan malam ini. Tautan selain YouTube/Vimeo
                     diabaikan saat ditampilkan, sama seperti video paket. --}}
                <input type="url" :name="'accommodations[' + idx + '][video]'" x-model="row.video"
                       placeholder="Video hotel (YouTube, opsional)"
                       class="sm:w-56 px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-toba-green focus:border-transparent transition">
                {{-- Foto lama ikut terkirim sebagai hidden: tanpa ini, menyimpan
                     ulang tanpa memilih berkas baru akan menghapus fotonya. --}}
                <input type="hidden" :name="'accommodations[' + idx + '][image]'" :value="row.image || ''">
                <div class="flex items-center gap-2 shrink-0">
                    <template x-if="row.image">
                        <img :src="'/storage/' + String(row.image).replace(/^\/?storage\//, '')"
                             @click="$dispatch('zoom-image', { url: '/storage/' + String(row.image).replace(/^\/?storage\//, ''), title: row.name || 'Foto Hotel' })"
                             class="w-10 h-10 rounded-lg object-cover border border-gray-200 cursor-zoom-in hover:opacity-80 transition"
                             title="Klik untuk zoom foto hotel">
                    </template>
                    <label class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-900 hover:text-white text-slate-600 flex items-center justify-center transition cursor-pointer" title="Foto hotel">
                        <i class="fas fa-camera text-xs"></i>
                        <input type="file" accept="image/*" :name="'accommodation_files[' + idx + ']'" class="hidden">
                    </label>
                    <button type="button" @click="nights.splice(idx, 1)"
                            class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white flex items-center justify-center transition">
                        <i class="fas fa-trash-alt text-xs"></i>
                    </button>
                </div>
            </div>
        </template>

        <button type="button" @click="nights.push({ night: nights.length + 1, name: '', class: '', image: '' })"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-900 hover:text-white text-slate-700 text-[10px] font-black uppercase tracking-widest transition">
            <i class="fas fa-plus text-[10px]"></i> Tambah Malam
        </button>
    </div>

    {{-- ============ PEMBEDA KHUSUS PAKET ============ --}}
    <div x-data="{ poin: @js($highlightRows) }">
        <label class="block text-sm font-bold text-gray-700 mb-2">Kenapa Paket Ini Berbeda</label>

        <template x-for="(row, idx) in poin" :key="'usp' + idx">
            <div class="flex flex-col sm:flex-row gap-2 mb-2">
                <input type="text" :name="'highlights[' + idx + '][title]'" x-model="row.title"
                       placeholder="Judul poin (mis. Titik pandang yang tidak didatangi operator lain)"
                       class="sm:w-72 px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-toba-green focus:border-transparent transition">
                <input type="text" :name="'highlights[' + idx + '][text]'" x-model="row.text"
                       placeholder="Penjelasan singkat (opsional)"
                       class="flex-1 px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-toba-green focus:border-transparent transition">
                <button type="button" @click="poin.splice(idx, 1)"
                        class="shrink-0 w-10 h-10 rounded-xl bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white flex items-center justify-center transition">
                    <i class="fas fa-trash-alt text-xs"></i>
                </button>
            </div>
        </template>

        <button type="button" @click="poin.push({ title: '', text: '' })"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-900 hover:text-white text-slate-700 text-[10px] font-black uppercase tracking-widest transition">
            <i class="fas fa-plus text-[10px]"></i> Tambah Poin
        </button>
        <p class="text-[11px] text-gray-400 mt-2 italic">* Menggantikan blok "Kenapa Kami Berbeda" bawaan situs — tapi HANYA di paket ini. Dikosongkan berarti paket ini memakai poin situs seperti sekarang. Isi dengan hal yang cuma benar untuk paket ini; poin yang sama-sama berlaku di semua paket lebih tepat ditulis sekali di Pengaturan CMS.</p>
        <p class="text-[11px] text-gray-400 mt-1 italic">* Baris tanpa judul tidak disimpan. Menghapus baris di sini menghapus poinnya.</p>
    </div>

    {{-- ============ PETA ============ --}}
    <div>
        <label class="block text-sm font-bold text-gray-700 mb-2">Peta Lokasi</label>
        <textarea name="mapEmbed" rows="3" placeholder="Tempel kode <iframe> dari Google Maps, tautan peta, atau koordinat: -2.6845, 98.8756"
                  class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-toba-green focus:border-transparent transition font-mono">{{ old('mapEmbed', $package->mapEmbed ?? '') }}</textarea>
        <p class="text-[11px] text-gray-400 mt-2 italic">* Hanya domain Google yang diterima. Yang diambil cuma alamat petanya — kode HTML yang ditempel tidak pernah dicetak apa adanya ke halaman publik.</p>
    </div>

    {{-- ============ BROSUR ============ --}}
    <div>
        <label class="block text-sm font-bold text-gray-700 mb-2">Brosur PDF</label>
        @if($package?->brochure)
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl border border-gray-200 mb-3">
                <i class="fas fa-file-pdf text-rose-500"></i>
                <a href="{{ $package->brochureUrl() }}" target="_blank" rel="noopener"
                   class="text-xs text-slate-600 hover:text-toba-green truncate flex-1 transition">{{ basename($package->brochure) }}</a>
                <label class="flex items-center gap-2 cursor-pointer shrink-0">
                    <input type="checkbox" name="remove_brochure" value="1"
                           class="w-4 h-4 rounded border-gray-300 text-rose-600 focus:ring-rose-500">
                    <span class="text-[9px] font-black uppercase tracking-widest text-rose-500">Hapus</span>
                </label>
            </div>
        @endif
        <input type="file" name="brochure_file" accept="application/pdf"
               class="block w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:uppercase file:tracking-widest file:bg-slate-900 file:text-white hover:file:bg-slate-800 transition cursor-pointer">
        <p class="text-[11px] text-gray-400 mt-2 italic">* Maks {{ round(maxUploadKb(20480) / 1024) }} MB. Mengunggah berkas baru menggantikan brosur lama.</p>
    </div>

    {{-- ============ GAMBAR INFORMASI HARGA ============ --}}
    <div>
        <label class="block text-sm font-bold text-gray-700 mb-1">Gambar Informasi Harga</label>

        <!-- Design Guidelines Guide -->
        <div class="mb-3 p-3.5 rounded-xl bg-green-50 border border-green-200 text-xs text-slate-700 space-y-1.5 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 font-black text-slate-900 text-[11px] uppercase tracking-wider">
                    <i class="fas fa-ruler-combined text-toba-green"></i>
                    <span>Standar Desain Brosur / Gambar Informasi Harga</span>
                </div>
                <button type="button" @click="$dispatch('open-image-guide')"
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-green-700 hover:bg-green-800 text-white font-bold text-[10px] transition shadow-sm">
                    <i class="fas fa-table-list text-[9px]"></i>
                    <span>Lihat Semua Ukuran</span>
                </button>
            </div>
            <p class="text-[11px] leading-relaxed text-slate-600">
                • <strong>Dimensi Rekomendasi:</strong> <span class="font-bold text-slate-900">1200 &times; 900 px</span> (Rasio <strong>4:3</strong>), minimal 1024 &times; 768 px.<br>
                • <strong>Margin Aman (Safe Zone):</strong> Berikan ruang kosong minimal <strong>40 px</strong> di keempat sisi agar tabel/angka harga tidak terpotong di layar HP.<br>
                • <strong>Tipografi &amp; Kontras:</strong> Gunakan font tebal dengan kontras tinggi terhadap latar belakang. Format didukung: JPG, PNG, WebP (maks 15 MB).
            </p>
        </div>

        @if($package?->priceImage)
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl border border-gray-200 mb-3 bg-slate-50/50">
                <div class="relative group cursor-zoom-in shrink-0"
                     @click="$dispatch('zoom-image', { url: '{{ $package->price_image_url }}', title: 'Gambar Informasi Harga' })"
                     title="Klik untuk zoom pratinjau">
                    <img src="{{ $package->price_image_url }}" alt="Pratinjau gambar harga"
                         class="w-20 aspect-[4/3] object-contain bg-white rounded-lg border border-gray-200 group-hover:opacity-80 transition">
                    <div class="absolute inset-0 flex items-center justify-center bg-black/20 opacity-0 group-hover:opacity-100 transition rounded-lg">
                        <i class="fas fa-search-plus text-white text-xs"></i>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold text-slate-800 truncate">{{ basename($package->priceImage) }}</p>
                    <button type="button" @click="$dispatch('zoom-image', { url: '{{ $package->price_image_url }}', title: 'Gambar Informasi Harga' })"
                            class="inline-flex items-center gap-1 text-[11px] text-toba-green font-semibold hover:underline mt-0.5">
                        <i class="fas fa-expand-alt text-[10px]"></i>
                        <span>Lihat &amp; Zoom Gambar</span>
                    </button>
                </div>
                <label class="flex items-center gap-2 cursor-pointer shrink-0">
                    <input type="checkbox" name="remove_price_image" value="1"
                           class="w-4 h-4 rounded border-gray-300 text-rose-600 focus:ring-rose-500">
                    <span class="text-[9px] font-black uppercase tracking-widest text-rose-500">Hapus</span>
                </label>
            </div>
        @endif
        <input type="file" name="price_image_file" accept="image/jpeg,image/png,image/jpg,image/webp"
               class="block w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:uppercase file:tracking-widest file:bg-slate-900 file:text-white hover:file:bg-slate-800 transition cursor-pointer">
        <p class="text-[11px] text-gray-400 mt-2 italic">* Tampil di kartu paket, tepat di bawah tombol Booking &amp; WhatsApp. Bingkainya 4:3 — pakai gambar berbentuk 4:3 (misal 1200&times;900) supaya tidak ada bilah kosong di sisi. Maks 15 MB. Kosongkan bila paket ini tidak punya gambar harga.</p>
        <p class="text-[11px] text-amber-600 mt-1 italic">* Paket yang sudah punya Harga Bertingkat TIDAK memakai gambar ini — kartunya menampilkan tabel harga grosir yang dibuat dari tingkatan itu. Tabelnya ikut berubah kalau harganya diubah dan ikut mengikuti mata uang tamu, jadi tidak pernah basi seperti gambar. Gambar ini hanya untuk paket yang belum punya tingkatan harga.</p>
    </div>
</div>
