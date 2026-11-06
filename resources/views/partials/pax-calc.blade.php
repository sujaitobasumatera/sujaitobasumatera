{{-- Mini kalkulator pax (dewasa/anak) + estimasi total + tombol booking.
     Butuh $xdata = string ekspresi Alpine
     "paxCalc(hargaDewasaMYR, hargaAnakMYR|null, slug, tiersHargaGrosir)".
     Dipakai oleh <x-package-card> (nilai PHP) dan kartu grid /tour/packages (nilai pkg.*).

     $priceImage (opsional) = string ekspresi Alpine yang menghasilkan URL
     gambar informasi harga, atau nilai kosong. Sengaja ekspresi, bukan URL
     mentah: pemanggil PHP mengirim @js($package->price_image_url), sedangkan
     grid /tour/packages mengirim `pkg.price_image_url` dari objek JS-nya --
     satu jalur kode untuk dua sumber data. --}}
<div x-data="{{ $xdata }}" class="border-t border-slate-100 px-4 py-3 space-y-2">
    {{-- Mini Currency Switcher --}}
    <div class="flex items-center justify-between pb-1 text-[10px] select-none" x-data>
        <span class="text-slate-400 font-semibold">{{ __('Kalkulator Pax') }}</span>
        <div class="inline-flex items-center gap-0.5 bg-slate-100 p-0.5 rounded-md border border-slate-200/80">
            <button type="button" @click="AppCurrency.setCurrency('MYR')"
                    :class="AppCurrency.currency === 'MYR' ? 'bg-white text-green-800 font-bold shadow-2xs' : 'text-slate-500 hover:text-slate-800'"
                    class="px-1.5 py-0.5 rounded text-[9px] transition">
                MYR
            </button>
            <button type="button" @click="AppCurrency.setCurrency('IDR')"
                    :class="AppCurrency.currency === 'IDR' ? 'bg-white text-green-800 font-bold shadow-2xs' : 'text-slate-500 hover:text-slate-800'"
                    class="px-1.5 py-0.5 rounded text-[9px] transition">
                IDR
            </button>
            <button type="button" @click="AppCurrency.setCurrency('SGD')"
                    :class="AppCurrency.currency === 'SGD' ? 'bg-white text-green-800 font-bold shadow-2xs' : 'text-slate-500 hover:text-slate-800'"
                    class="px-1.5 py-0.5 rounded text-[9px] transition">
                SGD
            </button>
        </div>
    </div>

    {{-- Baris Dewasa --}}
    <div class="flex items-center justify-between gap-2">
        <div class="min-w-0">
            <p class="text-[11px] font-semibold text-slate-700 leading-tight">{{ __('Dewasa') }}</p>
            <p class="text-[10px] text-slate-400 leading-tight" x-text="fmt(adultDisplay) + ' /{{ __('org') }}'"></p>
        </div>
        <div class="flex items-center gap-1 shrink-0">
            <button type="button" @click="decA()" aria-label="{{ __('Kurangi dewasa') }}" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-toba-green hover:text-white text-slate-600 flex items-center justify-center text-base font-bold leading-none transition select-none">&minus;</button>
            <input type="text" inputmode="numeric" x-model.number="adults" @change="normA()" @blur="normA()" aria-label="{{ __('Jumlah dewasa') }}" class="w-8 text-center text-[13px] font-bold text-slate-800 bg-transparent border-0 outline-none p-0">
            <button type="button" @click="incA()" aria-label="{{ __('Tambah dewasa') }}" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-toba-green hover:text-white text-slate-600 flex items-center justify-center text-base font-bold leading-none transition select-none">+</button>
        </div>
    </div>

    {{-- Baris Anak-anak --}}
    <div class="flex items-center justify-between gap-2">
        <div class="min-w-0">
            <p class="text-[11px] font-semibold text-slate-700 leading-tight">{{ __('Anak-anak') }}</p>
            {{-- Angka anaknya sudah tampil, tapi tanpa keterangan ia terbaca
                 sebagai harga acak. Aturannya tetap: separuh harga dewasa,
                 mengikuti tarif grosir yang sedang berlaku. --}}
            <p class="text-[10px] text-slate-400 leading-tight" x-text="fmt(childDisplay) + ' /{{ __('org') }}'"></p>
            <p class="text-[9px] text-slate-400 leading-tight">{{ __('50% harga dewasa') }}</p>
        </div>
        <div class="flex items-center gap-1 shrink-0">
            <button type="button" @click="decC()" aria-label="{{ __('Kurangi anak') }}" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-toba-green hover:text-white text-slate-600 flex items-center justify-center text-base font-bold leading-none transition select-none">&minus;</button>
            <input type="text" inputmode="numeric" x-model.number="children" @change="normC()" @blur="normC()" aria-label="{{ __('Jumlah anak') }}" class="w-8 text-center text-[13px] font-bold text-slate-800 bg-transparent border-0 outline-none p-0">
            <button type="button" @click="incC()" aria-label="{{ __('Tambah anak') }}" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-toba-green hover:text-white text-slate-600 flex items-center justify-center text-base font-bold leading-none transition select-none">+</button>
        </div>
    </div>

    {{-- Micro Trust Badges --}}
    <div class="flex items-center justify-between text-[10px] font-semibold text-slate-500 pt-1.5 border-t border-slate-100">
        <span class="inline-flex items-center gap-1 text-green-800">
            <span class="material-symbols-outlined text-[13px] text-green-700">restaurant</span>
            {{ __('100% Halal Food') }}
        </span>
        <span class="inline-flex items-center gap-1 text-slate-700">
            <span class="material-symbols-outlined text-[13px] text-slate-600">directions_car</span>
            {{ __('Private Trip') }}
        </span>
    </div>

    {{-- Estimasi total + tombol Booking & WhatsApp.
         Dua tombol tidak muat di sisa baris total pada lebar kartu (~300px),
         jadi totalnya dapat barisnya sendiri dan tombolnya turun jadi dua kolom
         sama lebar. --}}
    <div class="pt-2 border-t border-dashed border-slate-200 space-y-2">
        <div class="min-w-0">
            <p class="text-[8.5px] uppercase tracking-widest text-slate-400 leading-tight">{{ __('Estimasi Total') }}</p>
            <p class="text-[15px] font-extrabold text-toba-green leading-tight" x-text="fmt(total)"></p>
        </div>
        <div class="grid grid-cols-2 gap-2">
            <a :href="bookingUrl" class="inline-flex items-center justify-center gap-1 bg-toba-green text-white text-[11px] font-bold uppercase tracking-wider px-3 py-2 rounded-lg hover:bg-primary-container active:scale-95 transition">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                {{ __('Booking') }}
            </a>
            {{-- Langsung ke WhatsApp, pesannya sudah terisi jumlah pax & total
                 yang sedang tampil di kalkulator ini. --}}
            <a :href="waUrl" target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center justify-center gap-1.5 border border-toba-green text-toba-green text-[11px] font-bold uppercase tracking-wider px-3 py-2 rounded-lg hover:bg-toba-green hover:text-white active:scale-95 transition">
                <x-icon name="whatsapp" class="w-3.5 h-3.5 shrink-0" />
                {{ __('WhatsApp') }}
            </a>
        </div>

        {{-- Tabel harga grosir, menggantikan gambar informasi harga.

             Sumbernya `tiers` milik paxCalc -- array yang SAMA yang dipakai
             tierFor() untuk menghitung total di atasnya. Jadi angka di tabel
             ini tidak mungkin berbeda dari angka yang ditagih: keduanya membaca
             satu larik yang sama, bukan dua salinan yang bisa berselisih.

             Kenapa menggantikan gambar: gambar harga adalah teks yang dijepret.
             Di kartu selebar ~300px ia harus diketuk dulu untuk terbaca, tidak
             bisa dipilih atau dicari, tidak ikut diterjemahkan, tidak ikut
             berubah kalau mata uang tamu bukan ringgit, dan tidak terbaca oleh
             pembaca layar maupun mesin pencari. Tabel ini semuanya bisa.

             Baris yang sedang berlaku disorot mengikuti jumlah dewasa yang
             dipilih tamu, jadi ia sekaligus menjelaskan dari mana angka total
             di atas berasal. --}}
        <template x-if="tiers.length">
            <div class="rounded-lg border border-slate-100 overflow-hidden">
                <p class="flex items-center gap-1.5 px-2.5 py-1.5 bg-toba-green/5 text-[9.5px] font-bold uppercase tracking-widest text-toba-green">
                    <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M16 11c1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 3-1.34 3-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                    {{ __('Harga Khusus (Lebih Banyak Lebih Murah!)') }}
                </p>
                <ul>
                    <template x-for="(t, i) in tiers" :key="i">
                        {{-- Dibandingkan lewat min_pax, BUKAN lewat kesamaan objek
                             (tierFor(adults) === t). Alpine membungkus tiap objek
                             dalam Proxy, jadi identitas objek di sini tidak dijamin
                             sama dengan yang dikembalikan tierFor -- perbandingannya
                             bisa diam-diam selalu bernilai salah, dan sorotannya
                             tidak pernah muncul tanpa satu pun pesan galat. --}}
                        <li class="flex items-center justify-between gap-2 px-2.5 py-1.5 border-b border-slate-100 last:border-b-0 text-[11px] leading-snug"
                            :class="tierFor(adults) && tierFor(adults).min_pax === t.min_pax ? 'bg-toba-green/10 font-bold text-toba-green' : 'text-slate-600'">
                            <span x-text="t.min_pax === t.max_pax ? t.min_pax + ' Pax' : t.min_pax + ' – ' + t.max_pax + ' Pax'"></span>
                            {{-- Satuannya teks Blade biasa di luar ekspresi JS.
                                 Menempelkannya ke dalam string JS lewat Blade akan
                                 memutus string itu begitu ada terjemahan yang
                                 mengandung apostrof. --}}
                            <span class="shrink-0 whitespace-nowrap"><span x-text="fmt(t.price)"></span>/{{ __('org') }}</span>
                        </li>
                    </template>
                </ul>
            </div>
        </template>

        {{-- Gambar informasi harga tetap disediakan untuk paket yang BELUM punya
             tier. Object-contain, bukan object-cover seperti media kartu di atas:
             isinya teks/angka, dan cover memotong tepinya begitu rasio gambarnya
             meleset dari 4:3. Dibungkus <a> supaya bisa diketuk ke ukuran penuh. --}}
        @if(! empty($priceImage))
        <template x-if="!tiers.length && {{ $priceImage }}">
            <a :href="{{ $priceImage }}"
               @click.prevent="$dispatch('zoom-image', { url: {{ $priceImage }}, title: '{{ __('Informasi & Brosur Harga') }}' })"
               class="block rounded-lg overflow-hidden border border-slate-100 bg-slate-50 hover:border-slate-200 transition relative group cursor-zoom-in"
               aria-label="{{ __('Lihat informasi harga ukuran penuh') }}"
               title="{{ __('Klik untuk perbesar informasi harga') }}">
                <div class="relative aspect-[4/3]">
                    <img :src="{{ $priceImage }}" alt="{{ __('Informasi harga') }}" loading="lazy" decoding="async"
                         class="absolute inset-0 w-full h-full object-contain">
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors flex items-center justify-center opacity-0 group-hover:opacity-100">
                        <span class="px-2.5 py-1 rounded-md bg-slate-900/80 text-white text-[10px] font-bold tracking-wider uppercase backdrop-blur-sm flex items-center gap-1 shadow-md">
                            <span class="material-symbols-outlined text-xs">zoom_in</span>
                            <span>{{ __('Perbesar') }}</span>
                        </span>
                    </div>
                </div>
            </a>
        </template>
        @endif
    </div>
</div>
