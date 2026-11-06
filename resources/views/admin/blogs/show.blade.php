@extends('admin.layout')

@section('title', 'Detail Artikel: ' . $blog->title)
@section('page-title', 'Preview Artikel Blog')

@section('content')
<div class="max-w-5xl mx-auto space-y-6 pb-14">
    <!-- Action Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.blogs.index') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white border border-slate-100 text-slate-400 hover:bg-slate-900 hover:text-white transition shadow-sm">
                <i class="fas fa-arrow-left text-xs"></i>
            </a>
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Blog Preview</h1>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Review konten sebelum dipublikasikan</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('tour.blog.detail', $blog->slug) }}" target="_blank" class="px-6 py-3.5 bg-white border border-slate-200 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 transition">
                <i class="fas fa-external-link mr-2"></i> Lihat di Web
            </a>
            <a href="{{ route('admin.blogs.edit', $blog) }}" class="px-8 py-3.5 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-800 transition shadow-xl shadow-slate-100">
                <i class="fas fa-pencil mr-2 text-xs"></i> Edit Artikel
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
        <!-- Content Column -->
        <div class="lg:col-span-8 space-y-6">
            <!-- Article Header -->
            <div class="bg-white rounded-[3rem] p-10 lg:p-16 border border-slate-50 shadow-sm space-y-8">
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1 bg-toba-green/10 text-toba-green rounded-full text-[9px] font-black uppercase tracking-widest">{{ $blog->category }}</span>
                        <span class="text-[9px] font-bold text-slate-300 uppercase tracking-widest">
                            {{ $blog->published_at ? $blog->published_at->format('d M Y') : 'Draft / Not Scheduled' }}
                        </span>
                    </div>
                    <h2 class="text-4xl font-black text-slate-900 leading-tight tracking-tight">{{ $blog->title }}</h2>
                    <div class="flex items-center gap-3 pt-4 border-t border-slate-50">
                        <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 font-black text-[10px]">
                            {{ strtoupper(substr($blog->author, 0, 1)) }}
                        </div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Oleh <span class="text-slate-900">{{ $blog->author }}</span></p>
                    </div>
                </div>

                @if($blog->image)
                    <div class="aspect-video rounded-[2.5rem] overflow-hidden border border-slate-100 shadow-xl">
                        <img src="{{ $blog->image_url }}" alt="{{ $blog->title }}" class="w-full h-full object-cover">
                    </div>
                @endif

                <div class="prose prose-slate max-w-none prose-sm font-medium leading-relaxed text-slate-600">
                    {!! $blog->content !!}
                </div>
            </div>
        </div>

        <!-- Meta Sidebar -->
        <div class="lg:col-span-4 space-y-8">
            <!-- SEO Status -->
            <div class="bg-slate-900 rounded-[2.5rem] p-8 text-white space-y-6 shadow-2xl">
                <div class="flex items-center justify-between">
                    <h3 class="text-[11px] font-black uppercase tracking-widest text-white/40">Status Artikel</h3>
                    <span class="px-3 py-1 rounded-full text-[8px] font-black uppercase tracking-widest {{ $blog->status === 'published' ? 'bg-green-500' : 'bg-amber-500' }}">
                        {{ $blog->status }}
                    </span>
                </div>
                
                <div class="space-y-6">
                    <div class="space-y-2">
                        <p class="text-[9px] font-black text-white/30 uppercase tracking-widest">Meta Title</p>
                        <p class="text-xs font-bold leading-normal">{{ $blog->metaTitle ?? $blog->title }}</p>
                    </div>
                    <div class="space-y-2">
                        <p class="text-[9px] font-black text-white/30 uppercase tracking-widest">Meta Description</p>
                        <p class="text-[10px] font-medium leading-relaxed text-white/60 italic">
                            "{{ $blog->metaDescription ?? $blog->excerpt }}"
                        </p>
                    </div>
                    <div class="space-y-2 pt-6 border-t border-white/5">
                        <p class="text-[9px] font-black text-white/30 uppercase tracking-widest">Slug (URL)</p>
                        <div class="p-3 bg-white/5 rounded-xl text-[10px] font-mono text-green-400 break-all">
                            /blogs/{{ $blog->slug }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Share Info -->
            <div class="bg-white rounded-[2.5rem] p-8 border border-slate-50 shadow-sm space-y-6 text-center">
                <div class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto text-slate-300">
                    <i class="fas fa-chart-simple text-sm"></i>
                </div>
                <div>
                    <h4 class="text-[11px] font-black text-slate-900 uppercase tracking-widest">Statistik Publikasi</h4>
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">
                        Diterbitkan pada {{ $blog->published_at ? $blog->published_at->format('d/m/Y H:i') : '-' }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
