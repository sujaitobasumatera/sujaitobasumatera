@extends('layouts.app')

@section('title', __('Blog & Inspirasi Wisata – Sujai Toba Sumatera'))
@section('description', __('Tips, panduan, dan cerita menarik untuk rencana liburan Anda ke Sumatera Utara.'))
@section('keywords', 'blog wisata toba, artikel travel sumatera utara, tips liburan danau toba, cerita perjalanan sujai')

@section('content')
<div 
    x-data="{ 
        activeCategory: '{{ __('Semua') }}', 
        searchQuery: '', 
        posts: @js($posts),
        get categories() {
            const cats = ['{{ __('Semua') }}', ...new Set(this.posts.map(p => p.category).filter(Boolean))];
            return cats;
        },
        
        {{-- Kategori dan pencarian tersimpan di URL supaya hasilnya bisa
             dibagikan dan bertahan saat pembaca kembali dari satu artikel. --}}
        init() {
            const params = new URLSearchParams(window.location.search);
            const cat = params.get('kategori');
            if (cat && this.categories.includes(cat)) this.activeCategory = cat;
            this.searchQuery = params.get('q') || '';
            ['activeCategory', 'searchQuery'].forEach(key => {
                this.$watch(key, () => this.syncUrl());
            });
        },

        syncUrl() {
            const params = new URLSearchParams();
            if (this.activeCategory !== '{{ __('Semua') }}') params.set('kategori', this.activeCategory);
            if ((this.searchQuery || '').trim() !== '') params.set('q', this.searchQuery.trim());
            const query = params.toString();
            window.history.replaceState({}, '', query ? window.location.pathname + '?' + query : window.location.pathname);
        },

        {{-- Satu aturan penyaring untuk dua pemakai: cocokFilter menyembunyikan
             kartu yang dicetak server, filteredPosts hanya untuk menghitung
             keadaan kosong. Ditulis sekali supaya keduanya mustahil berselisih. --}}
        cocokFilter(category, title, content) {
            const cat = this.activeCategory === '{{ __('Semua') }}' || category === this.activeCategory;
            const q = (this.searchQuery || '').toLowerCase();
            const cari = (title || '').toLowerCase().includes(q)
                || (content || '').toLowerCase().includes(q);
            return cat && cari;
        },

        get filteredPosts() {
            return this.posts.filter(p => this.cocokFilter(p.category, p.title, p.content));
        },
        locale: '{{ session('locale', 'my') === 'my' ? 'ms-MY' : (session('locale') === 'en' ? 'en-SG' : 'id-ID') }}'
    }"
    class="bg-surface min-h-screen pb-14 font-body-md text-on-background selection:bg-primary-container selection:text-on-primary-container"
>
    <!-- Cinematic Premium Hero Section -->
    <div class="relative h-[60dvh] flex items-center overflow-hidden bg-primary">
        @php
            $heroImage = imageUrl($siteSettings['cms_tour']['hero_image_url'] ?? $siteSettings['cms_landing']['tour_image_url'] ?? null, asset('images/sumut/sumatra_panorama.webp'));
        @endphp
        <img src="{{ $heroImage }}" alt="Blog Hero" class="absolute inset-0 w-full h-full object-cover opacity-45 animate-subtle-zoom">
        <div class="absolute inset-0 bg-gradient-to-r from-primary via-primary/50 to-transparent"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-surface via-transparent to-transparent"></div>

        <div class="relative z-10 w-full max-w-7xl mx-auto px-5 md:px-8 pt-10">
            <div class="max-w-4xl">
                <x-breadcrumb :dark="true" class="mb-4" :items="[
                    ['label' => __('Blog')],
                ]" />
                <div class="flex items-center space-x-2 mb-4 animate-fade-in-down">
                    <span class="inline-flex items-center gap-2 px-4 py-1.5 bg-secondary-container/20 backdrop-blur-md border border-secondary/30 text-secondary-container text-[10px] font-black uppercase tracking-[0.25em] rounded-full">
                        {{ __('JOURNAL & STORIES') }}
                    </span>
                </div>
                <h1 class="text-4xl md:text-7xl font-bold text-white tracking-tight leading-[1.05] mb-6 animate-fade-in-up">
                    Inspirasi & <br />
                    <span class="text-secondary-fixed">{{ __('Eksplorasi Toba') }}</span>
                </h1>
                <p class="text-surface-container-highest text-sm md:text-lg font-light max-w-2xl leading-relaxed animate-fade-in-up delay-100">
                    {{ __('Temukan tips, panduan mendalam, dan cerita inspiratif dari setiap sudut Sumatera Utara untuk menemani rencana petualangan Anda.') }}
                </p>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="max-w-7xl mx-auto px-5 md:px-8 -mt-16 relative z-30">
        <div class="bg-white/95 backdrop-blur-md rounded-3xl shadow-xl p-6 border border-outline-variant/20 animate-in fade-in zoom-in duration-1000 delay-300">
            <div class="flex flex-col lg:flex-row items-center justify-between gap-6">
                <!-- Category Filters -->
                <div class="flex flex-wrap justify-center lg:justify-start gap-2.5">
                    <template x-for="cat in categories" :key="cat">
                        <button
                            @click="activeCategory = cat"
                            :class="activeCategory === cat ? 'bg-primary text-white shadow-lg border-primary' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container hover:text-primary border border-outline-variant/30'"
                            class="px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-wider transition duration-300 whitespace-nowrap cursor-pointer hover:-translate-y-0.5"
                            x-text="cat"
                        ></button>
                    </template>
                </div>
                
                <!-- Search Box -->
                <div class="relative group w-full lg:w-80">
                    <div class="absolute left-4 top-1/2 -translate-y-1/2 text-outline group-focus-within:text-secondary transition-colors pointer-events-none flex items-center">
                        <span class="material-symbols-outlined text-xl">search</span>
                    </div>
                    <input
                        type="text"
                        placeholder="{{ __('Cari topik atau artikel...') }}"
                        x-model="searchQuery"
                        class="w-full pl-12 pr-4 py-3.5 bg-surface-container-low border border-outline-variant/30 rounded-2xl focus:ring-2 focus:ring-secondary/40 focus:border-secondary font-body-md text-on-background placeholder:text-outline/70 text-sm outline-none transition"
                    >
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-5 md:px-8 mt-8 md:mt-10">

        {{-- Artikel dicetak server, penyaringnya tetap milik Alpine.

             Sebelumnya seluruh daftar digambar dari template x-for, jadi judul
             artikel TIDAK PERNAH ada sebagai teks di HTML -- hanya sebagai data
             mentah di dalam atribut x-data. Untuk situs yang jelas mengandalkan
             tulisan (ada FAQ internasional, artikel berbahasa Inggris soal
             Kualanamu dan makanan halal), itu melawan tujuan menulisnya.

             Satu perbedaan perilaku yang disengaja: artikel sorotan sekarang
             SELALU yang terbaru, bukan "yang pertama lolos saring". Ia ikut
             disembunyikan kalau tidak cocok dengan filter. Tanpa filter,
             hasilnya sama persis dengan sebelumnya. --}}
        @php
            $cadanganGambar = asset('images/sumut/sumatra_panorama.webp');
            $sorotan = $posts->first();
            $sisa = $posts->slice(1);
            $tglPanjang = fn ($p) => \Illuminate\Support\Carbon::parse($p->createdAt)->translatedFormat('j F Y');
        @endphp

        <!-- Featured Post -->
        @if($sorotan)
            @php $gbrSorotan = $sorotan->image_url ?: $cadanganGambar; @endphp
            <article x-show="cocokFilter({{ \Illuminate\Support\Js::from($sorotan->category) }}, {{ \Illuminate\Support\Js::from($sorotan->title) }}, {{ \Illuminate\Support\Js::from($sorotan->content) }})"
                     class="group relative bg-primary rounded-3xl md:rounded-[2.5rem] overflow-hidden shadow-2xl transition duration-700 mb-6 md:mb-10 min-h-[420px] md:min-h-[550px] flex flex-col justify-end border border-outline-variant/20">
                <img src="{{ $gbrSorotan }}" alt="{{ $sorotan->translated_title }}"
                    @if($ss = imageSrcset($gbrSorotan)) srcset="{{ $ss }}" sizes="(max-width: 1023px) 100vw, 1100px" @endif
                    fetchpriority="high" decoding="async"
                    class="absolute inset-0 w-full h-full object-cover opacity-50 group-hover:scale-105 transition-transform duration-[2s] ease-out">

                <!-- Overlays -->
                <div class="absolute inset-0 bg-gradient-to-t from-primary via-primary/40 to-transparent"></div>
                <div class="absolute inset-0 bg-gradient-to-r from-primary/60 to-transparent"></div>

                <div class="relative z-10 p-8 md:p-16 max-w-3xl animate-in fade-in slide-in-from-bottom-12 duration-1000">
                    <div class="flex items-center gap-4 text-[10px] font-black uppercase tracking-widest text-white mb-6">
                        @if(trim((string) $sorotan->category) !== '')
                            <span class="px-4 py-1.5 bg-secondary-container text-on-secondary-fixed-variant rounded-full text-[9px]">{{ $sorotan->category }}</span>
                        @endif
                        <span class="text-surface-container-highest/80 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-sm">calendar_today</span>
                            <time datetime="{{ \Illuminate\Support\Carbon::parse($sorotan->createdAt)->toDateString() }}">{{ $tglPanjang($sorotan) }}</time>
                        </span>
                    </div>
                    <h2 class="text-2xl sm:text-3xl md:text-5xl font-bold text-white mb-5 md:mb-6 group-hover:text-secondary-fixed transition-colors leading-[1.1] tracking-tight">{{ $sorotan->translated_title }}</h2>
                    <p class="text-surface-container-highest text-sm md:text-base font-light mb-8 line-clamp-2 leading-relaxed opacity-90">{{ Str::limit(strip_tags($sorotan->translated_excerpt ?: $sorotan->content), 180) }}</p>

                    <a href="/tour/blog/{{ $sorotan->slug ?? $sorotan->id }}" class="inline-flex items-center gap-3 bg-white text-primary px-8 py-4 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-secondary hover:text-white transition duration-300 shadow-md hover:-translate-y-0.5 group/btn">
                        <span>{{ __('Baca Cerita Lengkap') }}</span>
                        <span class="material-symbols-outlined text-sm transition-transform duration-300 group-hover/btn:translate-x-1">arrow_forward</span>
                    </a>
                </div>
            </article>
        @endif

        <!-- Blog Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-10">
            @foreach($sisa as $i => $post)
                @php $gbrPost = $post->image_url ?: $cadanganGambar; @endphp
                <article x-show="cocokFilter({{ \Illuminate\Support\Js::from($post->category) }}, {{ \Illuminate\Support\Js::from($post->title) }}, {{ \Illuminate\Support\Js::from($post->content) }})"
                         class="group flex flex-col h-full bg-white rounded-3xl overflow-hidden shadow-lg border border-outline-variant/20 hover:border-secondary/30 transition duration-500 hover:-translate-y-1.5 animate-in fade-in slide-in-from-bottom-8 duration-1000"
                         style="animation-delay: {{ $i * 100 }}ms">
                    <a href="/tour/blog/{{ $post->slug ?? $post->id }}" class="block relative overflow-hidden h-64">
                        <img src="{{ $gbrPost }}" alt="{{ $post->translated_title }}"
                            @if($ss = imageSrcset($gbrPost)) srcset="{{ $ss }}" sizes="(max-width: 639px) 100vw, (max-width: 1023px) 50vw, 400px" @endif
                            loading="lazy" decoding="async"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-[1.5s] ease-out">
                        <div class="absolute inset-0 bg-primary/10 group-hover:bg-primary/0 transition-colors"></div>
                        @if(trim((string) $post->category) !== '')
                        <div class="absolute top-4 left-4">
                            <span class="bg-white/95 backdrop-blur-md text-primary px-3 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest shadow-sm">{{ $post->category }}</span>
                        </div>
                        @endif
                    </a>

                    <div class="flex-1 flex flex-col p-8">
                        <div class="flex items-center gap-1.5 text-secondary text-[9px] font-black uppercase tracking-widest mb-3">
                            <span class="material-symbols-outlined text-xs">calendar_today</span>
                            <time datetime="{{ \Illuminate\Support\Carbon::parse($post->createdAt)->toDateString() }}">{{ $tglPanjang($post) }}</time>
                        </div>
                        <h2 class="text-xl font-bold text-primary mb-3 group-hover:text-secondary transition-colors leading-tight line-clamp-2 tracking-tight">{{ $post->translated_title }}</h2>
                        <p class="text-on-surface-variant text-xs leading-relaxed mb-6 line-clamp-3 font-light flex-grow">{{ Str::limit(strip_tags($post->translated_excerpt ?: $post->content), 160) }}</p>

                        <div class="pt-6 border-t border-outline-variant/20 mt-auto">
                            <a href="/tour/blog/{{ $post->slug ?? $post->id }}" class="inline-flex items-center gap-2 text-primary font-black text-[10px] uppercase tracking-widest group/link hover:text-secondary transition-colors">
                                <span>{{ __('Baca Selengkapnya') }}</span>
                                <span class="material-symbols-outlined text-sm transition-transform duration-300 group-hover/link:translate-x-1">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <!-- Empty State -->
        <div x-show="filteredPosts.length === 0" class="text-center py-10 bg-white rounded-[2.5rem] border border-outline-variant/20 shadow-xl animate-in fade-in zoom-in duration-700">
            <div class="w-20 h-20 bg-surface-container rounded-full flex items-center justify-center mx-auto mb-6 text-outline/50 shadow-inner">
                <span class="material-symbols-outlined text-3xl font-light">menu_book</span>
            </div>
            <h3 class="text-2xl font-headline-md text-primary mb-3 tracking-tight">{{ __('Artikel Belum Tersedia') }}</h3>
            <p class="text-on-surface-variant text-xs font-light max-w-xs mx-auto mb-8 leading-relaxed">{{ __('Kami sedang menyusun cerita perjalanan menarik untuk Anda. Silakan coba kategori lain atau reset pencarian.') }}</p>
            <button @click="activeCategory = '{{ __('Semua') }}'; searchQuery = ''"
                class="bg-primary text-white px-8 py-4 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-primary-container transition duration-300 shadow-md hover:-translate-y-0.5 cursor-pointer">
                {{ __('Reset Jurnal') }}
            </button>
        </div>
    </div>
</div>

<style>
    @keyframes subtle-zoom {
        from { transform: scale(1); }
        to { transform: scale(1.05); }
    }
    .animate-subtle-zoom {
        animation: subtle-zoom 20s infinite alternate ease-in-out;
    }
</style>
@endsection
