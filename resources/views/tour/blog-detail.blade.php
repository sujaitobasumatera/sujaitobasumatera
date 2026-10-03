@extends('layouts.app')

@php
    $coverExif = null;
    if (!empty($post->image)) {
        $clean = ltrim($post->image, '/');
        if (str_starts_with($clean, 'storage/')) {
            $clean = substr($clean, 8);
        }
        $media = \App\Models\Media::where('path', $clean)->orWhere('path', $post->image)->first();
        if ($media && $media->exif_data) {
            $coverExif = $media->exif_data;
        }
    }
@endphp

@section('title', ($post->translated_title ?? 'Blog') . ' – Sujai Toba Sumatera')
{{-- Isi artikel adalah HTML dari editor. Str::limit atas HTML mentah memotong
     160 huruf PERTAMA -- yang sebagian besar berisi tag pembuka, bukan kalimat,
     sehingga pratinjau tautan terbaca sebagai kode. Ringkasan dipakai lebih
     dulu kalau penulisnya sudah menuliskannya. --}}
@php
    $metaArtikel = trim(preg_replace('/\s+/', ' ',
        preg_replace('/<[^>]*>/', ' ', html_entity_decode((string) ($post->excerpt ?: $post->content ?? ''), ENT_QUOTES | ENT_HTML5))
    ));
@endphp
@section('description', \Illuminate\Support\Str::limit($metaArtikel, 160))

@section('og_image', ogBannerUrl($post))

@push('schema')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@type": "BlogPosting",
  "headline": "{{ $post->translated_title }}",
  "image": [
    "{{ $post->image }}"
  ],
  "datePublished": "{{ date('c', strtotime($post->createdAt)) }}",
  "author": [{
      "@type": "Organization",
      "name": "Sujai Toba Sumatera"
    }]
}
</script>
@endpush

@section('content')
<div class="bg-surface min-h-screen pb-14 font-body-md text-on-background selection:bg-primary-container selection:text-on-primary-container">

    {{-- ====== IMMERSIVE CINEMATIC HERO ====== --}}
    <div class="relative h-[55dvh] w-full overflow-hidden bg-primary">
        {!! responsiveImage($post->image, 'absolute inset-0 w-full h-full object-cover opacity-50 animate-subtle-zoom', $post->translated_title, 'fetchpriority="high" decoding="async"') !!}
        
        {{-- Gradient overlays --}}
        <div class="absolute inset-0 bg-gradient-to-t from-surface via-transparent to-primary/40"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-primary/70 via-primary/20 to-transparent"></div>

        {{-- Hero content --}}
        <div class="relative z-10 h-full max-w-5xl mx-auto px-5 md:px-8 flex flex-col justify-center pt-8">
            <div class="animate-in fade-in slide-in-from-bottom-12 duration-1000">
                {{-- Back button --}}
                <a href="/tour/blog" 
                   class="inline-flex items-center gap-2 text-white/80 hover:text-white font-bold text-xs uppercase tracking-wider mb-8 transition group">
                    <div class="w-8 h-8 rounded-full bg-white/10 backdrop-blur-md flex items-center justify-center border border-white/20 group-hover:bg-secondary group-hover:border-secondary transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                            <path d="M19 12H5M12 19l-7-7 7-7"/>
                        </svg>
                    </div>
                    {{ __('Kembali ke Jurnal') }}
                </a>
                
                <x-breadcrumb :dark="true" class="mb-5" :items="[
                    ['label' => __('Blog'), 'url' => route('tour.blog')],
                    ['label' => $post->translated_title ?? $post->title],
                ]" />

                {{-- Category & date badges --}}
                <div class="flex flex-wrap items-center gap-3 mb-6">
                    <span class="px-4 py-1.5 bg-secondary text-on-secondary rounded-lg text-[10px] font-bold uppercase tracking-wider shadow-sm">
                        {{ $post->translated_category }}
                    </span>
                    <span class="px-4 py-1.5 bg-white/10 backdrop-blur-md border border-white/20 text-white rounded-lg text-[10px] font-bold uppercase tracking-wider">
                        {{ \Carbon\Carbon::parse($post->createdAt)->locale(session('locale', 'my') === 'en' ? 'en' : (session('locale', 'my') === 'my' ? 'ms' : 'id'))->translatedFormat('d F Y') }}
                    </span>
                </div>

                <h1 class="text-3xl md:text-5xl font-bold text-white tracking-tight leading-[1.1] drop-shadow-sm">
                    {{ $post->translated_title }}
                </h1>
            </div>
        </div>
    </div>

    {{-- ====== READING AREA ====== --}}
    <div class="max-w-7xl mx-auto px-5 md:px-8 -mt-16 relative z-20">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-10">

            {{-- ── Main article body ── --}}
            <div class="lg:col-span-8">
                <article class="bg-white rounded-3xl p-6 md:p-14 shadow-sm border border-outline-variant/30">
                    
                    {{-- Author meta --}}
                    <div class="flex items-center gap-4 mb-6 pb-8 border-b border-outline-variant/30">
                        <div class="w-12 h-12 rounded-xl bg-primary text-on-primary flex items-center justify-center font-bold text-lg">
                            S
                        </div>
                        <div>
                            <p class="font-label-caps text-[9px] text-secondary uppercase tracking-wider mb-0.5">{{ __('Ditulis Oleh') }}</p>
                            <p class="text-base font-bold text-on-surface tracking-tight font-headline-md">{{ __('Tim Redaksi Sujai Toba Sumatera') }}</p>
                        </div>
                    </div>

                    @if($coverExif)
                    <div class="mb-6 p-5 bg-slate-50 border border-slate-200/60 rounded-2xl flex flex-wrap gap-4 items-center justify-between text-xs text-slate-500 animate-in fade-in slide-in-from-top-4 duration-500 shadow-sm">
                        <div class="flex items-center gap-3">
                            <span class="text-lg">📸</span>
                            <div>
                                <p class="font-semibold text-slate-700">Metadata Foto Jurnal</p>
                                <p class="text-slate-500">
                                    @if(!empty($coverExif['camera_brand']) || !empty($coverExif['camera_model']))
                                        {{ $coverExif['camera_brand'] ?? '' }} {{ $coverExif['camera_model'] ?? '' }}
                                    @endif
                                    @if(!empty($coverExif['aperture'])) • {{ $coverExif['aperture'] }} @endif
                                    @if(!empty($coverExif['iso'])) • ISO {{ $coverExif['iso'] }} @endif
                                    @if(!empty($coverExif['shutter_speed'])) • {{ $coverExif['shutter_speed'] }} @endif
                                </p>
                            </div>
                        </div>
                        @if(!empty($coverExif['gps']['lat']) && !empty($coverExif['gps']['lng']))
                        <a href="https://www.google.com/maps/search/?api=1&query={{ $coverExif['gps']['lat'] }},{{ $coverExif['gps']['lng'] }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-50 text-green-700 hover:bg-green-100 rounded-lg font-semibold tracking-wide transition-colors">
                            <span class="material-symbols-outlined text-[14px]">location_on</span>
                            {{ __('Titik Lokasi') }}
                        </a>
                        @endif
                    </div>
                    @endif

                    {{-- Article body --}}
                    <div class="prose prose-lg md:prose-xl max-w-none break-words [&_a]:break-all text-slate-700 font-body-md leading-[1.8] tracking-[0.01em]
                                prose-headings:font-headline-md prose-headings:text-primary prose-headings:font-semibold
                                prose-a:text-green-600 prose-a:font-semibold prose-a:no-underline hover:prose-a:underline
                                prose-strong:text-slate-900 prose-strong:font-bold
                                prose-blockquote:border-l-4 prose-blockquote:border-green-500 prose-blockquote:bg-green-50/50 prose-blockquote:py-2 prose-blockquote:px-6 prose-blockquote:text-slate-700 prose-blockquote:not-italic prose-blockquote:rounded-r-xl
                                prose-img:rounded-2xl prose-img:shadow-sm">
                        @if(!empty($post->content) && strlen($post->content) > 10)
                            {!! nl2br($post->content) !!}
                        @else
                            <p class="text-slate-400 italic text-center py-6">{{ $post->translated_excerpt }}</p>
                        @endif
                    </div>

                    {{-- Tags --}}
                    @if(isset($post->tags) && count($post->tags) > 0)
                        <div class="mt-6 pt-8 border-t border-outline-variant/30 flex flex-wrap gap-2">
                            @foreach($post->tags as $tag)
                                {{-- Gaya hover dihapus: tag ini bukan tautan (belum ada
                                     halaman arsip per tag), jadi warna yang berubah saat
                                     disentuh menjanjikan klik yang tidak akan terjadi. --}}
                                <span class="px-4 py-1.5 bg-surface-container-low text-on-surface-variant rounded-lg text-[10px] font-bold uppercase tracking-wider border border-outline-variant/30 cursor-default">
                                    #{{ $tag }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    {{-- Premium share bar --}}
                    <div class="mt-6 p-6 bg-surface-container-low border border-outline-variant/30 rounded-3xl flex flex-col md:flex-row items-center justify-between gap-6">
                        <div>
                            <h4 class="text-base font-bold font-headline-md text-on-surface tracking-tight mb-1">{{ __('Bagikan Inspirasi Ini') }}</h4>
                            <p class="text-xs text-on-surface-variant font-body-md">{{ __('Bantu orang lain menemukan petualangan impian mereka.') }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="https://wa.me/?text={{ urlencode(__('Baca artikel ini dari Sujai Toba Sumatera: ') . $post->translated_title . ' ' . url()->current()) }}" 
                               target="_blank"
                               class="w-10 h-10 bg-toba-green text-white rounded-xl flex items-center justify-center shadow-sm hover:scale-105 transition">
                                <x-icon name="whatsapp" class="w-5 h-5" />
                            </a>
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" 
                               target="_blank"
                               class="w-10 h-10 bg-blue-600 text-white rounded-xl flex items-center justify-center shadow-sm hover:scale-105 transition">
                                <x-icon name="facebook" class="w-4 h-4" />
                            </a>
                                     <button @click="navigator.clipboard.writeText(window.location.href)"
                                         class="w-10 h-10 bg-white text-outline border border-outline-variant rounded-xl flex items-center justify-center shadow-sm hover:bg-slate-50 transition"
                                         title="{{ __('Salin link') }}">
                                <span class="material-symbols-outlined text-[18px]">link</span>
                            </button>
                        </div>
                    </div>
                </article>
            </div>

            {{-- ── Sidebar ── --}}
            <div class="lg:col-span-4">
                <div class="sticky top-28 space-y-8 animate-in fade-in slide-in-from-right-12 duration-1000">
                    
                    {{-- CTA Card (dark) --}}
                    <div class="bg-primary rounded-3xl p-8 text-on-primary shadow-xl border border-white/5 relative overflow-hidden">
                        <div class="absolute -top-24 -right-24 w-64 h-64 bg-secondary/10 rounded-full blur-[80px]"></div>
                        <div class="relative z-10">
                            <span class="font-label-caps text-[10px] text-secondary-fixed uppercase tracking-wider mb-4 block">{{ __('Eksplorasi Sekarang') }}</span>
                            <h3 class="text-2xl font-headline-lg font-normal text-on-primary mb-4 tracking-tight leading-tight">
                                {!! __('Siap Untuk <br/>Menjelajah?') !!}
                            </h3>
                            <p class="text-on-primary-container/70 font-body-md text-xs mb-8 leading-relaxed">
                                {{ __('Wujudkan cerita petualangan Anda sendiri. Pilih paket wisata yang paling sesuai dengan jiwa petualang Anda.') }}
                            </p>
                            <a href="/tour/packages" 
                               class="w-full flex items-center justify-center gap-2 py-3.5 bg-secondary text-on-secondary rounded-xl font-bold font-label-caps text-xs uppercase tracking-wider hover:bg-secondary/90 transition duration-300 shadow-md group">
                                {{ __('Lihat Paket Wisata') }}
                                <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                    <path d="M5 12h14M12 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>

                    {{-- Author box --}}
                    <div class="bg-white rounded-3xl p-8 border border-outline-variant/30 shadow-sm">
                        <p class="font-label-caps text-[9px] text-outline uppercase tracking-wider mb-4">{{ __('Penulis Resmi') }}</p>
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-secondary/10 text-secondary flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path d="M12 20h9M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-bold text-on-surface text-sm font-body-md">Sujai Toba Sumatera</p>
                                <p class="font-label-caps text-[9px] text-secondary uppercase tracking-wider">{{ __('Editorial Team') }}</p>
                            </div>
                        </div>
                        <p class="text-xs text-on-surface-variant font-body-md leading-relaxed">
                            {{ __('Kami berdedikasi untuk memberikan panduan perjalanan paling akurat dan inspiratif di Sumatera Utara.') }}
                        </p>
                    </div>

                    {{-- Quick links --}}
                    <div class="bg-surface-container-low rounded-3xl p-6 border border-outline-variant/20">
                        <p class="font-label-caps text-[9px] text-outline uppercase tracking-wider mb-4">{{ __('Jelajahi') }}</p>
                        <div class="space-y-2">
                            <a href="/tour/packages" class="flex items-center justify-between p-3 rounded-xl hover:bg-surface-container transition-colors group">
                                <span class="text-xs text-on-surface-variant group-hover:text-on-surface font-body-md">{{ __('Paket Wisata') }}</span>
                                <span class="material-symbols-outlined text-outline group-hover:text-secondary text-sm transition-colors">arrow_forward</span>
                            </a>
                            <a href="/tour/gallery" class="flex items-center justify-between p-3 rounded-xl hover:bg-surface-container transition-colors group">
                                <span class="text-xs text-on-surface-variant group-hover:text-on-surface font-body-md">{{ __('Galeri Foto') }}</span>
                                <span class="material-symbols-outlined text-outline group-hover:text-secondary text-sm transition-colors">arrow_forward</span>
                            </a>
                            <a href="/about" class="flex items-center justify-between p-3 rounded-xl hover:bg-surface-container transition-colors group">
                                <span class="text-xs text-on-surface-variant group-hover:text-on-surface font-body-md">{{ __('Tentang Kami') }}</span>
                                <span class="material-symbols-outlined text-outline group-hover:text-secondary text-sm transition-colors">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ====== RELATED POSTS ====== --}}
        @if(count($relatedPosts) > 0)
            <div class="mt-8 md:mt-12 pt-6 md:pt-8 border-t border-outline-variant/30">
                <div class="flex items-center gap-3 mb-6 md:mb-6">
                    <span class="w-10 h-px bg-secondary"></span>
                    <span class="text-[10px] font-bold text-secondary uppercase tracking-[0.25em]">{{ __('Inspirasi Lainnya') }}</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
                    @foreach($relatedPosts as $rp)
                        <article class="group cursor-pointer" onclick="window.location.href='/tour/blog/{{ $rp->slug ?? $rp->id }}'">
                            <div class="relative aspect-[16/10] rounded-3xl overflow-hidden mb-5 border border-outline-variant/20 shadow-sm">
                                <img src="{{ $rp->image_url }}" alt="{{ $rp->translated_title }}" @if($__ss = imageSrcset($rp->image_url)) srcset="{{ $__ss }}" sizes="(max-width: 639px) 100vw, 380px" @endif 
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-[1.5s]">
                                <div class="absolute inset-0 bg-gradient-to-t from-primary/70 to-transparent opacity-0 group-hover:opacity-100 transition duration-500 flex items-end p-5">
                                    <span class="font-label-caps text-[9px] text-secondary-fixed uppercase tracking-widest flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">arrow_forward</span>
                                        {{ __('Baca Artikel') }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <span class="font-label-caps text-[9px] text-secondary uppercase tracking-wider mb-2 block">{{ $rp->translated_category }}</span>
                                <h4 class="text-base font-bold font-headline-md text-on-surface mb-2 group-hover:text-secondary transition-colors leading-tight tracking-tight line-clamp-2">
                                    {{ $rp->translated_title }}
                                </h4>
                                <span class="text-xs text-on-surface-variant group-hover:text-secondary transition font-body-md flex items-center gap-1">
                                    {{ __('Selanjutnya') }}
                                    <svg class="w-3.5 h-3.5 text-secondary" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                        <path d="M5 12h14M12 5l7 7-7 7"/>
                                    </svg>
                                </span>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
