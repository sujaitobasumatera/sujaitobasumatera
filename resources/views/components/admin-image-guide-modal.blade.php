{{-- Modal Panduan Standar Ukuran Gambar & Rasio Desain Admin --}}
<div x-data="{
        openGuide: false,
        copiedText: '',
        copyDimension(text) {
            navigator.clipboard.writeText(text);
            this.copiedText = text;
            setTimeout(() => { this.copiedText = ''; }, 2000);
        }
    }"
    @open-image-guide.window="openGuide = true"
    @keydown.escape.window="openGuide = false"
>
    <!-- Modal Backdrop & Dialog -->
    <template x-if="openGuide">
        <div class="fixed inset-0 z-[9998] flex items-center justify-center p-3 sm:p-6 bg-slate-900/70 backdrop-blur-sm select-none"
             @click.self="openGuide = false"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             x-cloak>

            <div class="relative w-full max-w-5xl bg-white rounded-3xl shadow-2xl border border-slate-100 flex flex-col max-h-[92vh] overflow-hidden"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">

                {{-- Modal Header --}}
                <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-2xl bg-green-100 text-green-800 flex items-center justify-center shadow-sm">
                            <i class="fas fa-ruler-combined text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 tracking-tight leading-tight">Panduan Standar Ukuran Desain Gambar</h3>
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Sujai Toba Sumatera &bull; Spesifikasi Dimensi &amp; Rasio Aset Visual</p>
                        </div>
                    </div>
                    <button type="button" @click="openGuide = false"
                            class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-rose-500 hover:text-white text-slate-400 flex items-center justify-center transition"
                            title="Tutup (Esc)">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="p-6 overflow-y-auto custom-scroll space-y-6">

                    {{-- Quick Tip Alert --}}
                    <div class="flex items-start gap-3 p-4 rounded-2xl bg-green-50 border border-green-200 text-green-950 text-xs">
                        <i class="fas fa-circle-info text-green-600 text-base mt-0.5 shrink-0"></i>
                        <div class="leading-relaxed">
                            <p class="font-bold mb-0.5">Tips Desainer:</p>
                            Klik pada angka ukuran rekomendasi (misal <span class="bg-white/80 px-1.5 py-0.5 rounded font-mono font-bold text-green-800">1600 &times; 1200 px</span>) untuk menyalin dimensi ke clipboard. Format didukung: <strong>JPG, PNG, WebP</strong> (maks 15 MB). Sistem server otomatis mengonversi ke WebP responsif.
                        </div>
                    </div>

                    {{-- Spesifikasi Tabel --}}
                    <div class="overflow-x-auto rounded-2xl border border-slate-200 shadow-sm">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-900 text-white uppercase text-[10px] tracking-wider">
                                    <th class="py-3.5 px-4 font-black">Jenis Gambar</th>
                                    <th class="py-3.5 px-3 font-black text-center">Rasio</th>
                                    <th class="py-3.5 px-4 font-black">Ukuran Rekomendasi</th>
                                    <th class="py-3.5 px-3 font-black">Ukuran Minimum</th>
                                    <th class="py-3.5 px-3 font-black text-center">Safe Zone</th>
                                    <th class="py-3.5 px-5 font-black">Catatan Desain</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-700 bg-white">
                                {{-- Baris 1: Galeri Foto Paket --}}
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-4 px-4 font-bold text-slate-900">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                            <span>Galeri Foto Paket</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-3 text-center">
                                        <span class="px-2.5 py-1 bg-green-100 text-green-800 rounded-lg font-mono font-bold text-[11px]">4:3</span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <button type="button" @click="copyDimension('1600x1200')"
                                                class="group inline-flex items-center gap-1.5 font-mono font-bold text-green-700 hover:text-green-900 hover:underline">
                                            <span>1600 &times; 1200 px</span>
                                            <i class="far fa-copy text-[10px] opacity-40 group-hover:opacity-100"></i>
                                        </button>
                                    </td>
                                    <td class="py-4 px-3 font-mono text-slate-500">1200 &times; 900 px</td>
                                    <td class="py-4 px-3 text-center font-medium">10% dari tepi</td>
                                    <td class="py-4 px-5 leading-relaxed text-slate-600">
                                        Foto ke-1 otomatis menjadi <strong>sampul kartu paket</strong> dan thumbnail WhatsApp. Objek utama letakkan di tengah.
                                    </td>
                                </tr>

                                {{-- Baris 2: Brosur / Info Harga --}}
                                <tr class="hover:bg-slate-50/80 transition bg-green-50/40">
                                    <td class="py-4 px-4 font-bold text-slate-900">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-green-600"></span>
                                            <span>Brosur / Info Harga</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-3 text-center">
                                        <span class="px-2.5 py-1 bg-green-100 text-green-800 rounded-lg font-mono font-bold text-[11px]">4:3</span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <button type="button" @click="copyDimension('1200x900')"
                                                class="group inline-flex items-center gap-1.5 font-mono font-bold text-green-700 hover:text-green-900 hover:underline">
                                            <span>1200 &times; 900 px</span>
                                            <i class="far fa-copy text-[10px] opacity-40 group-hover:opacity-100"></i>
                                        </button>
                                    </td>
                                    <td class="py-4 px-3 font-mono text-slate-500">1024 &times; 768 px</td>
                                    <td class="py-4 px-3 text-center font-bold text-amber-700">Min. 40 px</td>
                                    <td class="py-4 px-5 leading-relaxed text-slate-600">
                                        Ditampilkan utuh (<span class="font-mono text-[11px] bg-slate-100 px-1 py-0.5 rounded">contain</span>). Sisakan margin kosong 40 px di sekeliling agar angka harga tidak terpotong di HP.
                                    </td>
                                </tr>

                                {{-- Baris 3: Hero Slider Desktop --}}
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-4 px-4 font-bold text-slate-900">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                            <span>Hero Slider Desktop</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-3 text-center">
                                        <span class="px-2.5 py-1 bg-blue-100 text-blue-800 rounded-lg font-mono font-bold text-[11px]">16:9</span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <button type="button" @click="copyDimension('1920x1080')"
                                                class="group inline-flex items-center gap-1.5 font-mono font-bold text-green-700 hover:text-green-900 hover:underline">
                                            <span>1920 &times; 1080 px</span>
                                            <i class="far fa-copy text-[10px] opacity-40 group-hover:opacity-100"></i>
                                        </button>
                                    </td>
                                    <td class="py-4 px-3 font-mono text-slate-500">1600 &times; 900 px</td>
                                    <td class="py-4 px-3 text-center font-medium">15% dari tepi</td>
                                    <td class="py-4 px-5 leading-relaxed text-slate-600">
                                        Lanskap luas Danau Toba. Area tengah bawah sedikit digelapkan agar teks judul putih terbaca jelas.
                                    </td>
                                </tr>

                                {{-- Baris 4: Hero Slider Mobile --}}
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-4 px-4 font-bold text-slate-900">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                            <span>Hero Slider Mobile</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-3 text-center">
                                        <span class="px-2.5 py-1 bg-indigo-100 text-indigo-800 rounded-lg font-mono font-bold text-[11px]">4:5 / 1:1</span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <button type="button" @click="copyDimension('1080x1350')"
                                                class="group inline-flex items-center gap-1.5 font-mono font-bold text-green-700 hover:text-green-900 hover:underline">
                                            <span>1080 &times; 1350 px</span>
                                            <i class="far fa-copy text-[10px] opacity-40 group-hover:opacity-100"></i>
                                        </button>
                                    </td>
                                    <td class="py-4 px-3 font-mono text-slate-500">800 &times; 800 px</td>
                                    <td class="py-4 px-3 text-center font-medium">15% dari tepi</td>
                                    <td class="py-4 px-5 leading-relaxed text-slate-600">
                                        Foto vertikal/persegi agar penuh di layar smartphone tanpa terpotong berlebihan.
                                    </td>
                                </tr>

                                {{-- Baris 5: Foto Hotel / Penginapan --}}
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-4 px-4 font-bold text-slate-900">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                                            <span>Foto Hotel / Penginapan</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-3 text-center">
                                        <span class="px-2.5 py-1 bg-purple-100 text-purple-800 rounded-lg font-mono font-bold text-[11px]">4:3 / 1:1</span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <button type="button" @click="copyDimension('800x600')"
                                                class="group inline-flex items-center gap-1.5 font-mono font-bold text-green-700 hover:text-green-900 hover:underline">
                                            <span>800 &times; 600 px</span>
                                            <i class="far fa-copy text-[10px] opacity-40 group-hover:opacity-100"></i>
                                        </button>
                                    </td>
                                    <td class="py-4 px-3 font-mono text-slate-500">600 &times; 600 px</td>
                                    <td class="py-4 px-3 text-center font-medium">10% dari tepi</td>
                                    <td class="py-4 px-5 leading-relaxed text-slate-600">
                                        Foto tampak depan atau kamar hotel untuk rincian akomodasi per malam paket.
                                    </td>
                                </tr>

                                {{-- Baris 6: Foto Artikel Blog --}}
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-4 px-4 font-bold text-slate-900">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                            <span>Foto Artikel Blog</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-3 text-center">
                                        <span class="px-2.5 py-1 bg-amber-100 text-amber-800 rounded-lg font-mono font-bold text-[11px]">16:9</span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <button type="button" @click="copyDimension('1200x675')"
                                                class="group inline-flex items-center gap-1.5 font-mono font-bold text-green-700 hover:text-green-900 hover:underline">
                                            <span>1200 &times; 675 px</span>
                                            <i class="far fa-copy text-[10px] opacity-40 group-hover:opacity-100"></i>
                                        </button>
                                    </td>
                                    <td class="py-4 px-3 font-mono text-slate-500">960 &times; 540 px</td>
                                    <td class="py-4 px-3 text-center font-medium">10% dari tepi</td>
                                    <td class="py-4 px-5 leading-relaxed text-slate-600">
                                        Tema budaya Batak, kuliner khas, dan destinasi wisata Sumatera Utara.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Feedback Toast Salin Dimensi --}}
                    <div x-show="copiedText" x-cloak
                         class="text-center py-1.5 px-4 bg-slate-900 text-white rounded-xl text-xs font-bold w-max mx-auto animate-bounce">
                        Dimensi <span x-text="copiedText" class="text-green-400"></span> berhasil disalin ke clipboard!
                    </div>

                </div>

                {{-- Modal Footer --}}
                <div class="flex items-center justify-between px-6 py-4 border-t border-slate-100 bg-slate-50 shrink-0">
                    <p class="text-[11px] text-slate-400 italic">Dokumen ini selalu sinkron dengan panduan resmi di <span class="font-mono text-slate-600">docs/PANDUAN_DESAIN_GAMBAR.md</span></p>
                    <button type="button" @click="openGuide = false"
                            class="px-5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition">
                        Tutup Panduan
                    </button>
                </div>

            </div>
        </div>
    </template>
</div>
