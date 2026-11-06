@php
    $g = $siteSettings['general'] ?? [];
    $officeAddress = $g['office_address'] ?? 'Jl. Trimurti 109, Berastagi, Kabupaten Karo';
    $socials = array_filter([
        'facebook'  => $g['social_facebook'] ?? null,
        'tiktok'    => $g['social_tiktok'] ?? null,
        'youtube'   => $g['social_youtube'] ?? null,
        'instagram' => !empty($g['social_instagram'])
            ? 'https://instagram.com/' . str_replace('@', '', $g['social_instagram'])
            : null,
    ]);
    $activeLocale = session('locale', 'my');
    $locales = [
        'my' => '🇲🇾 MYR (Melayu)',
        'id' => '🇮🇩 IDR (Indonesia)',
        'en' => '🇸🇬 SGD (English)',
    ];
    $localeShort = ['my' => '🇲🇾 MYR', 'id' => '🇮🇩 IDR', 'en' => '🇸🇬 SGD'];

    // Satu sumber kebenaran untuk menu — desktop & strip mobile membacanya sama.
    $navLinks = [
        ['label' => __('Tentang Kami'),     'url' => '/about',         'active' => request()->is('about')],
        ['label' => __('Blog'),             'url' => '/tour/blog',     'active' => request()->is('tour/blog*')],
        // Tautannya memang menuju form pelacakan, bukan halaman kontak —
        // labelnya disesuaikan supaya menjanjikan apa yang benar-benar dibuka.
        ['label' => __('Lacak Booking'),    'url' => route('booking.track.form'), 'active' => request()->is('track-booking*')],
    ];

    // Strip mobile itu daftar datar — tanpa dropdown — jadi Home & Paket
    // ikut jadi item biasa di depan $navLinks.
    $mobileNav = array_merge([
        ['label' => __('Home'), 'url' => '/', 'active' => request()->is('/')],
        [
            'label'  => __('Paket Wisata Toba'),
            'url'    => '/tour/packages',
            'active' => request()->is('tour/packages*') || request()->is('tour/package/*'),
        ],
    ], $navLinks);
@endphp

<header
    x-data="{
        scrolled: false,
        contact: {
            phone: @js(\App\Helpers\ContactHelper::whatsappDisplay()),
            email: {{ \Illuminate\Support\Js::from(\App\Helpers\ContactHelper::email()) }},
            whatsapp: @js(\App\Helpers\ContactHelper::whatsappDigits())
        }
    }"
    class="relative w-full font-sans z-[100]"
>
    <!-- 1. Topbar (Minimalist Dark) -->
    <div class="hidden sm:block bg-slate-900 text-slate-300">
        <div class="max-w-[1320px] mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center gap-6 py-2.5 text-[11.5px] tracking-wide">
            <div class="flex items-center gap-4 min-w-0">
                <!-- Lokasi Kantor -->
                <div class="flex items-center gap-2 min-w-0 opacity-90 hover:opacity-100 hover:text-white transition-all">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span class="truncate">{{ $officeAddress }}</span>
                </div>
                <span class="w-px h-3 bg-slate-700 hidden md:block" aria-hidden="true"></span>
                <!-- Kontak Telepon Langsung -->
                <a href="tel:+{{ \App\Helpers\ContactHelper::whatsappDigits() }}" class="hidden md:flex items-center gap-1.5 opacity-90 hover:opacity-100 hover:text-white transition-all text-green-400 font-semibold shrink-0">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    <span>{{ \App\Helpers\ContactHelper::whatsappDisplay() }}</span>
                </a>
            </div>

            <!-- Sosial + Bahasa -->
            <div class="flex items-center gap-4 shrink-0">
                @if(count($socials))
                    <div class="flex items-center gap-3">
                        @foreach($socials as $name => $url)
                            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
                               aria-label="{{ ucfirst($name) }}"
                               class="text-slate-400 hover:text-white transition-colors duration-200">
                                <x-icon :name="$name" class="w-3.5 h-3.5" />
                            </a>
                        @endforeach
                    </div>
                    <span class="w-px h-3 bg-slate-700" aria-hidden="true"></span>
                @endif

                {{-- z-[130] harus LEBIH TINGGI daripada z-[120] milik <nav> di
                     bawahnya. Panel ini duduk di topbar tapi terbuka ke bawah,
                     melintasi batas topbar dan bertumpuk dengan isi nav --
                     tombol "Hubungi Kami" di sana menimpanya.

                     z-[200] pada panelnya sendiri TIDAK menolong: nilai itu
                     hanya berlaku di dalam konteks penumpukan yang dibuat div
                     ini, ia tidak bisa melompati induknya. Yang menentukan
                     adalah z div ini terhadap z milik <nav>. --}}
                <div x-data="{ open: false }" class="relative z-[130]">
                    <button @click="open = !open" type="button"
                            :aria-expanded="open" aria-haspopup="true"
                            class="flex items-center gap-1.5 text-slate-400 hover:text-white transition-colors duration-200 font-semibold uppercase tracking-wider">
                        <span>{{ $localeShort[$activeLocale] ?? $localeShort['my'] }}</span>
                        <svg class="w-3 h-3 transition-transform duration-200" :class="open && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" x-cloak @click.away="open = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="absolute right-0 mt-2 w-48 bg-white text-slate-700 rounded-lg shadow-xl shadow-slate-900/10 ring-1 ring-slate-200 py-1 text-xs font-medium overflow-hidden z-[200]">
                        @foreach($locales as $code => $label)
                            <a href="{{ route('change-locale', $code) }}"
                               class="flex items-center justify-between gap-2 px-4 py-2.5 transition-colors hover:bg-slate-50 {{ $activeLocale === $code ? 'text-toba-green font-semibold bg-slate-50' : 'text-slate-600' }}">
                                <span>{{ $label }}</span>
                                @if($activeLocale === $code)
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Main Nav Putih (sticky, clean) -->
    {{-- Padding vertikal dipegang baris logo, bukan <nav>, supaya strip menu
         mobile bisa menempel rapat di tepi bawah nav yang sticky. --}}
    <nav @scroll.window="scrolled = window.scrollY > 24"
         :class="scrolled && 'shadow-md'"
         class="sticky top-0 bg-white/95 backdrop-blur-md border-b border-slate-100 transition-all duration-300 z-[120]">
        <div class="max-w-[1320px] mx-auto px-4 sm:px-6 lg:px-8">
            <div :class="scrolled ? 'py-2.5' : 'py-3 md:py-3.5'"
                 class="flex justify-between items-center gap-4 py-3 md:py-3.5 transition-all duration-300">
                <!-- Logo -->
                <a href="/" aria-label="{{ $g['site_name'] ?? 'Sujai Tour' }} — Beranda"
                   class="group flex items-baseline gap-1 shrink-0 mr-4 focus-visible:outline-none rounded">
                    <span class="text-2xl md:text-[1.7rem] font-extrabold tracking-tight text-slate-900 uppercase leading-none transition-colors duration-300 group-hover:text-toba-green">SUJAI</span>
                    <span class="text-2xl md:text-[1.7rem] font-medium tracking-tight text-toba-green italic leading-none"
                          style="font-family: 'Brush Script MT', 'Segoe Script', 'Lucida Handwriting', cursive;">Tour</span>
                </a>

                <!-- Nav Links Desktop (Minimalist) -->
                <div class="hidden lg:flex items-center gap-8 text-[14px] font-medium text-slate-500">
                    @php $isHome = request()->is('/'); @endphp
                    <a href="/" @if($isHome) aria-current="page" @endif
                       class="group relative py-1 transition-colors duration-300 {{ $isHome ? 'text-slate-900 font-semibold' : 'hover:text-slate-900' }}">
                        {{ __('Home') }}
                        <span class="absolute left-1/2 -bottom-1 h-[2px] rounded-full bg-slate-900 transition-all duration-300 ease-out -translate-x-1/2 {{ $isHome ? 'w-4' : 'w-0 group-hover:w-4' }}"></span>
                    </a>

                    <!-- Dropdown Paket -->
                    @php $isPkg = request()->is('tour/packages*'); @endphp
                    <div x-data="{ openPkg: false }" @mouseenter="openPkg = true" @mouseleave="openPkg = false" class="relative">
                        <a href="/tour/packages" @if($isPkg) aria-current="page" @endif
                           :aria-expanded="openPkg"
                           class="group relative flex items-center gap-1.5 py-1 transition-colors duration-300 {{ $isPkg ? 'text-slate-900 font-semibold' : 'hover:text-slate-900' }}">
                            <span>{{ __('Paket Wisata Toba') }}</span>
                            <svg class="w-3.5 h-3.5 transition-transform duration-300 text-slate-400 group-hover:text-slate-900" :class="openPkg && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            <span class="absolute left-1/2 -bottom-1 h-[2px] rounded-full bg-slate-900 transition-all duration-300 ease-out -translate-x-1/2 {{ $isPkg ? 'w-4' : 'w-0 group-hover:w-4' }}"></span>
                        </a>
                        <!-- pt-4 for hover bridge -->
                        <div x-show="openPkg" x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="absolute left-0 top-full pt-4 w-72 z-[200]">
                            <div class="bg-white rounded-xl shadow-xl shadow-slate-900/10 ring-1 ring-slate-100 p-2 overflow-hidden">
                                <a href="/tour/packages" class="block px-4 py-2.5 text-[13px] font-semibold text-slate-800 rounded-lg hover:bg-slate-50 transition-colors">{{ __('Semua Paket Tour') }}</a>
                                @if(count($navPackages ?? []))
                                    <div class="my-2 border-t border-slate-100"></div>
                                    <div class="max-h-[60vh] overflow-y-auto space-y-0.5">
                                        @foreach($navPackages as $navPkg)
                                            <a href="/tour/package/{{ $navPkg->slug }}"
                                               class="block px-4 py-2.5 rounded-lg hover:bg-slate-50 transition-colors group/item">
                                                <span class="block text-[13px] font-medium text-slate-700 group-hover/item:text-slate-900">{{ $navPkg->translated_name }}</span>
                                                @if($navPkg->duration)
                                                    <span class="block mt-0.5 text-[11px] text-slate-400">{{ $navPkg->duration }}</span>
                                                @endif
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    @foreach($navLinks as $link)
                        <a href="{{ $link['url'] }}" @if($link['active']) aria-current="page" @endif
                           class="group relative py-1 transition-colors duration-300 {{ $link['active'] ? 'text-slate-900 font-semibold' : 'hover:text-slate-900' }}">
                            {{ $link['label'] }}
                            <span class="absolute left-1/2 -bottom-1 h-[2px] rounded-full bg-slate-900 transition-all duration-300 ease-out -translate-x-1/2 {{ $link['active'] ? 'w-4' : 'w-0 group-hover:w-4' }}"></span>
                        </a>
                    @endforeach
                </div>

                <div class="hidden lg:flex items-center gap-3 shrink-0 ml-6">
                    <!-- Wishlist Button Desktop -->
                    <a href="/tour/packages"
                       class="relative p-2 rounded-full text-slate-600 hover:text-red-500 hover:bg-slate-50 transition"
                       title="{{ __('Paket Tersimpan') }}"
                       aria-label="{{ __('Paket Tersimpan') }}">
                        <span class="material-symbols-outlined text-[22px]" style="font-variation-settings: 'FILL' 1;">favorite</span>
                        <span x-show="$store.wishlist && $store.wishlist.count() > 0"
                              x-text="$store.wishlist.count()"
                              x-cloak
                              class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-red-500 text-white text-[10px] font-extrabold flex items-center justify-center"></span>
                    </a>

                    <a :href="'https://wa.me/' + contact.whatsapp" target="_blank" rel="noopener noreferrer"
                       class="group inline-flex items-center gap-2.5 border border-toba-green text-toba-green hover:bg-toba-green hover:text-white px-6 py-2.5 rounded-full font-medium text-[13.5px] tracking-wide transition-all duration-300">
                        <span>{{ __('Hubungi Kami') }}</span>
                        <x-icon name="whatsapp" class="w-3 h-3 transition-transform duration-300 group-hover:scale-110" />
                    </a>
                </div>

                <!-- Aksi Mobile — menu pindah ke strip di bawah -->
                <div class="lg:hidden flex items-center gap-2 shrink-0">
                    <!-- Wishlist Button Mobile -->
                    <a href="/tour/packages"
                       class="relative w-8 h-8 rounded-full bg-slate-50 text-slate-600 hover:text-red-500 flex items-center justify-center border border-slate-200 active:scale-95 transition-all"
                       title="{{ __('Paket Tersimpan') }}"
                       aria-label="{{ __('Paket Tersimpan') }}">
                        <span class="material-symbols-outlined text-[16px]" style="font-variation-settings: 'FILL' 1;">favorite</span>
                        <span x-show="$store.wishlist && $store.wishlist.count() > 0"
                              x-text="$store.wishlist.count()"
                              x-cloak
                              class="absolute -top-1 -right-1 w-3.5 h-3.5 rounded-full bg-red-500 text-white text-[9px] font-extrabold flex items-center justify-center"></span>
                    </a>

                    <!-- Tombol Telepon Cepat Mobile -->
                    <a href="tel:+{{ \App\Helpers\ContactHelper::whatsappDigits() }}"
                       class="w-8 h-8 rounded-full bg-green-50 text-green-700 flex items-center justify-center border border-green-200 active:scale-95 transition-all"
                       title="{{ __('Telepon Langsung') }}"
                       aria-label="{{ __('Telepon Kami') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    </a>

                    {{-- Kembaran pemilih bahasa milik topbar. Topbar itu `hidden
                         sm:block`, jadi di bawah 640px pemilihnya lenyap sama
                         sekali; chip ini menutup lubang itu dan `sm:hidden`
                         supaya tidak muncul dobel begitu topbar kembali ada.

                         Sengaja TIDAK ditaruh di dalam strip menu: strip itu
                         `overflow-x-auto`, dan overflow di satu sumbu ikut
                         mengkliping sumbu satunya — panel dropdown akan
                         terpotong di tepi bawah strip. --}}
                    <div x-data="{ open: false }" class="sm:hidden relative">
                        <button @click="open = !open" type="button"
                                :aria-expanded="open" aria-haspopup="true"
                                aria-label="{{ __('Pilih bahasa & mata uang') }}"
                                class="flex items-center gap-1 px-2 py-1.5 rounded-lg text-[11px] font-bold uppercase tracking-wider text-slate-600 hover:bg-slate-100 active:scale-95 transition-all">
                            <span>{{ $localeShort[$activeLocale] ?? $localeShort['my'] }}</span>
                            <svg class="w-2.5 h-2.5 text-slate-400 transition-transform duration-200" :class="open && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" aria-hidden="true"><path d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-cloak @click.away="open = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="absolute right-0 mt-2 w-48 bg-white text-slate-700 rounded-xl shadow-xl shadow-slate-900/10 ring-1 ring-slate-200 py-1 text-[13px] font-medium overflow-hidden z-[200]">
                            @foreach($locales as $code => $label)
                                <a href="{{ route('change-locale', $code) }}"
                                   class="flex items-center justify-between gap-2 px-4 py-3 transition-colors hover:bg-slate-50 {{ $activeLocale === $code ? 'text-toba-green font-semibold bg-slate-50' : 'text-slate-600' }}">
                                    <span>{{ $label }}</span>
                                    @if($activeLocale === $code)
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <a :href="'https://wa.me/' + contact.whatsapp" target="_blank" rel="noopener noreferrer"
                       aria-label="{{ __('Hubungi Kami') }}"
                       class="w-9 h-9 rounded-full bg-slate-900 text-white flex items-center justify-center active:scale-95 transition-transform">
                        <x-icon name="whatsapp" class="w-4 h-4" />
                    </a>
                </div>
            </div>
        </div>

        {{-- Strip menu mobile: satu baris, geser horizontal kalau item melebihi
             lebar layar. Saat halaman dibuka, item aktif digeser ke tengah
             sendiri lewat scrollLeft (bukan scrollIntoView, yang ikut menggeser
             halaman secara vertikal). --}}
        <div class="lg:hidden border-t border-slate-100 overflow-x-auto no-scrollbar overscroll-x-contain"
             x-init="$nextTick(() => {
                 const item = $el.querySelector('[aria-current=page]');
                 if (item) $el.scrollLeft = item.offsetLeft - ($el.clientWidth - item.offsetWidth) / 2;
             })">
            <ul class="flex items-stretch whitespace-nowrap px-4 sm:px-6 gap-7 sm:gap-9 justify-start sm:justify-center">
                @foreach($mobileNav as $link)
                    <li>
                        <a href="{{ $link['url'] }}" @if($link['active']) aria-current="page" @endif
                           class="relative flex items-center py-3 text-[10.5px] font-bold uppercase tracking-[0.14em] transition-colors {{ $link['active'] ? 'text-toba-green' : 'text-slate-500 hover:text-slate-900' }}">
                            {{ $link['label'] }}
                            @if($link['active'])
                                <span class="absolute inset-x-0 bottom-0 h-[2px] rounded-full bg-toba-green"></span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </nav>

</header>
