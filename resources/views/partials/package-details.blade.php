{{-- Akordeon ringkasan isi paket di kartu: termasuk, tidak termasuk, rute.
     Butuh $xdata = string ekspresi Alpine
     "pkgDetails(includes, excludes, itinerary)".
     Butuh $uid  = ekspresi Alpine yang MENGHASILKAN id unik (bukan string
     biasa) -- di grid, partial ini dirender sekali lalu diulang oleh x-for,
     jadi id yang statis akan terpasang di semua kartu sekaligus dan
     aria-controls setiap tombol menunjuk panel kartu pertama.
     Dipakai oleh <x-package-card> (nilai PHP) dan kartu grid /tour/packages
     (nilai pkg.*) -- satu markup, dua sumber data, supaya keduanya tidak bisa
     menyimpang seperti harga kartu vs harga invoice dulu. --}}
<div x-data="{{ $xdata }}" x-show="!isEmpty" x-cloak class="border-t border-slate-100">
    <button type="button"
            @click="open = !open"
            :aria-expanded="open ? 'true' : 'false'"
            :aria-controls="{{ $uid }}"
            class="w-full flex items-center justify-between gap-2 px-4 py-2.5 text-left rounded-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-toba-green">
        <span class="text-[10.5px] font-bold uppercase tracking-widest text-slate-500">{{ __('Detail Paket') }}</span>
        <svg class="w-3.5 h-3.5 shrink-0 text-slate-400 transition-transform duration-200"
             :class="open ? 'rotate-180' : ''"
             fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    {{-- x-show biasa, bukan x-collapse: plugin @alpinejs/collapse tidak
         terpasang di proyek ini, jadi x-collapse hanya akan diam. --}}
    <div :id="{{ $uid }}" x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="px-4 pb-3 space-y-3">
        {{-- Rute: judul hari saja. Aktivitas per hari tetap di halaman detail. --}}
        <template x-if="days.length">
            <div>
                <p class="text-[9.5px] font-bold uppercase tracking-widest text-slate-400 mb-1">{{ __('Rute Perjalanan') }}</p>
                <ul class="space-y-0.5">
                    {{-- Label ditulis sebagai teks Blade dan hanya angkanya yang
                         di-bind. Menyusunnya di dalam literal string JS akan
                         pecah begitu ada terjemahan yang mengandung apostrof.
                         Kuncinya 'Hari ke-', bukan 'Hari': 'Hari' sudah dipakai
                         untuk durasi paket dan berarti "Days" dalam bahasa
                         Inggris -- "Days 1" bukan yang kita mau. --}}
                    <template x-for="d in days" :key="d.day">
                        <li class="flex gap-1.5 text-[11px] leading-snug text-slate-600">
                            <span class="shrink-0 font-semibold text-toba-green">{{ __('Hari ke-') }} <span x-text="d.day"></span></span>
                            <span class="min-w-0 truncate" x-text="d.title"></span>
                        </li>
                    </template>
                </ul>
            </div>
        </template>

        <template x-if="includes.length">
            <div>
                <p class="text-[9.5px] font-bold uppercase tracking-widest text-slate-400 mb-1">{{ __('Termasuk') }}</p>
                <ul class="space-y-0.5">
                    <template x-for="(item, i) in includes" :key="i">
                        <li class="flex items-start gap-1.5 text-[11px] leading-snug text-slate-600">
                            <svg class="w-3 h-3 mt-0.5 shrink-0 text-toba-green" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="min-w-0" x-text="item"></span>
                        </li>
                    </template>
                </ul>
            </div>
        </template>

        <template x-if="excludes.length">
            <div>
                <p class="text-[9.5px] font-bold uppercase tracking-widest text-slate-400 mb-1">{{ __('Tidak Termasuk') }}</p>
                <ul class="space-y-0.5">
                    <template x-for="(item, i) in excludes" :key="i">
                        <li class="flex items-start gap-1.5 text-[11px] leading-snug text-slate-500">
                            <svg class="w-3 h-3 mt-0.5 shrink-0 text-rose-400" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <span class="min-w-0" x-text="item"></span>
                        </li>
                    </template>
                </ul>
            </div>
        </template>
    </div>
</div>
