@extends('layouts.app')

@section('title', 'Kebijakan Privasi – Sujai Toba Sumatera')

@section('content')
<div class="bg-slate-50 min-h-screen pt-14 pb-12">
    <div class="max-w-4xl mx-auto px-5">
        <div class="bg-white rounded-3xl p-6 md:p-12 shadow-sm border border-slate-100 animate-in fade-in slide-in-from-bottom-8 duration-1000">
            <h1 class="text-3xl md:text-5xl font-bold text-slate-900 mb-6 tracking-tight">Kebijakan <span class="text-toba-green">Privasi</span></h1>
            
            <div class="prose prose-slate max-w-none text-slate-600 text-sm font-normal leading-relaxed">
                {{-- date('d F Y') membuat "terakhir diperbarui" selalu berbunyi
                     HARI INI, berapa pun lamanya teks ini tidak berubah. Untuk
                     dokumen hukum itu klaim yang keliru. Pakai tanggal pembaruan
                     yang sebenarnya bila ada, kalau tidak pakai tanggal tetap. --}}
                @php
                    $privacyUpdated = !empty($content['updated_at'])
                        ? \Illuminate\Support\Carbon::parse($content['updated_at'])->translatedFormat('d F Y')
                        : 'Juni 2025';
                @endphp
                <p class="mb-8 text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Terakhir diperbarui: {{ $privacyUpdated }}</p>
                
                @if(isset($content['content']))
                    {!! strip_tags($content['content'], '<p><h2><h3><h4><ul><ol><li><strong><em><a><br><blockquote><table><thead><tbody><tr><th><td>') !!}
                @else
                    <h3 class="text-slate-900 font-bold text-base mb-3 tracking-tight">Perlindungan Data</h3>
                    <p class="mb-6">Sujai Toba Sumatera berkomitmen untuk melindungi privasi pelanggan kami. Kami hanya mengumpulkan informasi yang diperlukan untuk memproses pemesanan Anda dan meningkatkan layanan kami.</p>
                    
                    <h3 class="text-slate-900 font-bold text-base mb-3 tracking-tight">Informasi yang Kami Kumpulkan</h3>
                    <p class="mb-6">Informasi yang kami kumpulkan meliputi nama, alamat email, nomor telepon, dan detail perjalanan yang diperlukan untuk koordinasi tour.</p>
                    
                    <h3 class="text-slate-900 font-bold text-base mb-3 tracking-tight">Penggunaan Informasi</h3>
                    <p class="mb-6">Data Anda tidak akan pernah dijual atau dibagikan kepada pihak ketiga untuk tujuan pemasaran tanpa persetujuan eksplisit dari Anda.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
