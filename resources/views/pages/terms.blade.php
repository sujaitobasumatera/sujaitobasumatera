@extends('layouts.app')

@section('title', 'Syarat dan Ketentuan – Sujai Toba Sumatera')
@section('description', 'Syarat dan ketentuan layanan paket wisata Sujai Toba Sumatera: pemesanan, pembayaran, pembatalan, dan tanggung jawab layanan perjalanan Danau Toba.')
@section('keywords', 'syarat ketentuan sujai toba sumatera, tour terms conditions lake toba, kebijakan pemesanan wisata, refund policy, lake toba tour package')

@section('content')
<div class="bg-slate-50 min-h-screen pt-14 pb-12">
    <div class="max-w-4xl mx-auto px-5">
        <div class="bg-white rounded-3xl p-6 md:p-12 shadow-sm border border-slate-100 animate-in fade-in slide-in-from-bottom-8 duration-1000">
            <h1 class="text-3xl md:text-5xl font-bold text-slate-900 mb-6 tracking-tight">Syarat &amp; <span class="text-toba-green">Ketentuan</span></h1>
            
            {{-- Identitas Perusahaan --}}
            <div class="mb-8 p-6 bg-slate-50 border border-slate-100 rounded-2xl text-xs text-slate-500 font-normal leading-relaxed">
                <p class="font-bold text-slate-700 mb-1">Sujai Toba Sumatera</p>
                <p>Nama Domain: <strong class="text-slate-800 font-medium">sujaitobasumatera.com</strong> — Brand resmi: <strong class="text-slate-800 font-medium">Sujai Toba Sumatera</strong></p>
                <p>Dioperasikan oleh Sujai Toba Sumatera, berdomisili di {{ $siteSettings['general']['office_address'] ?? $siteSettings['general']['address'] ?? 'Jl. Trimurti 109, Berastagi, Kabupaten Karo, Sumatera Utara' }}.</p>
                @php
                    $termsUpdated = !empty($content['updated_at'])
                        ? \Illuminate\Support\Carbon::parse($content['updated_at'])->translatedFormat('d F Y')
                        : 'Juni 2025';
                @endphp
                <p class="mt-2 text-[10px] text-slate-400">Terakhir diperbarui: {{ $termsUpdated }}</p>
            </div>
            
            <div class="prose prose-slate max-w-none text-slate-600 text-sm font-normal leading-relaxed">
                @if(isset($content['content']))
                    {!! strip_tags($content['content'], '<p><h2><h3><h4><ul><ol><li><strong><em><a><br><blockquote><table><thead><tbody><tr><th><td>') !!}
                @else
                    <h3 class="text-slate-900 font-bold text-base mb-3 tracking-tight">1. Pendaftaran &amp; Pemesanan</h3>
                    <p class="mb-6">Setiap pemesanan dianggap sah apabila dilakukan melalui website resmi <strong class="text-slate-900 font-medium">sujaitobasumatera.com</strong> atau melalui jalur komunikasi resmi (WhatsApp/Email). Kami berhak meminta uang muka (DP) sebesar 30–50% sebagai tanda jadi pemesanan paket wisata sebelum proses konfirmasi akomodasi dilakukan.</p>
                    
                    <h3 class="text-slate-900 font-bold text-base mb-3 tracking-tight">2. Pembayaran</h3>
                    <p class="mb-6">Pelunasan wajib dilakukan paling lambat 7 hari sebelum tanggal keberangkatan. Pembayaran dapat dilakukan melalui:</p>
                    <ul class="list-disc pl-5 mb-6 space-y-2">
                        <li>Transfer bank rekening resmi Sujai Toba Sumatera (BCA / BNI / Mandiri)</li>
                        <li>Transfer internasional via Wise (Transferwise) untuk tamu dari Singapura dan Malaysia</li>
                        <li>Mata uang yang diterima: IDR (Rupiah), MYR (Ringgit Malaysia), SGD (Singapore Dollar)</li>
                    </ul>
                    <p class="mb-6">Untuk informasi rekening dan detail pembayaran internasional, silakan kunjungi halaman <a href="/payment" class="text-toba-green font-semibold hover:underline">Cara Pembayaran</a>.</p>
                    
                    <h3 class="text-slate-900 font-bold text-base mb-3 tracking-tight">3. Pembatalan</h3>
                    <ul class="list-disc pl-5 mb-6 space-y-2">
                        <li>Pembatalan &gt; 14 hari sebelum keberangkatan: Pengembalian dana 100% (potong biaya admin Rp 50.000).</li>
                        <li>Pembatalan 7–14 hari sebelum keberangkatan: Pengembalian dana 50%.</li>
                        <li>Pembatalan &lt; 7 hari sebelum keberangkatan: Dana tidak dapat dikembalikan.</li>
                        <li>Pembatalan karena bencana alam atau force majeure: Pengembalian dana penuh atau penjadwalan ulang tanpa biaya.</li>
                    </ul>
                    
                    <h3 class="text-slate-900 font-bold text-base mb-3 tracking-tight">4. Tanggung Jawab &amp; Asuransi</h3>
                    <p class="mb-6">Sujai Toba Sumatera bertanggung jawab atas keselamatan dan kenyamanan tamu selama program berlangsung sesuai itinerary yang disepakati. Kami menyediakan asuransi perjalanan dasar untuk setiap tamu. Tamu disarankan untuk memiliki asuransi perjalanan pribadi yang mencakup evakuasi medis, terutama untuk tamu internasional.</p>
                    
                    <h3 class="text-slate-900 font-bold text-base mb-3 tracking-tight">5. Perubahan Jadwal</h3>
                    <p class="mb-6">Sujai Toba Sumatera berhak mengubah itinerary atau jadwal perjalanan apabila terjadi kondisi force majeure (bencana alam, penutupan akses oleh pemerintah, cuaca ekstrem, dll) demi keselamatan dan kenyamanan tamu. Perubahan akan diberitahukan paling lambat 24 jam sebelum keberangkatan.</p>
                    
                    <h3 class="text-slate-900 font-bold text-base mb-3 tracking-tight">6. Hak &amp; Kewajiban Tamu</h3>
                    <ul class="list-disc pl-5 mb-6 space-y-2">
                        <li>Tamu wajib mematuhi peraturan dan norma lokal di setiap destinasi yang dikunjungi.</li>
                        <li>Tamu wajib memiliki dokumen perjalanan yang sah (paspor, visa jika diperlukan).</li>
                        <li>Tamu bertanggung jawab atas barang bawaan pribadi masing-masing.</li>
                    </ul>
                    
                    <h3 class="text-slate-900 font-bold text-base mb-3 tracking-tight">7. Kontak &amp; Penyelesaian Sengketa</h3>
                    <p class="mb-6">Untuk pertanyaan, keluhan, atau penyelesaian sengketa, tamu dapat menghubungi kami melalui:</p>
                    <ul class="list-none pl-0 mb-6 space-y-2">
                        <li>📧 Email: <a href="mailto:{{ \App\Helpers\ContactHelper::email() }}" class="text-toba-green font-semibold hover:underline">{{ \App\Helpers\ContactHelper::email() }}</a></li>
                        <li>📱 WhatsApp 1: <a href="{{ \App\Helpers\ContactHelper::whatsappLink() }}" class="text-toba-green font-semibold hover:underline">{{ \App\Helpers\ContactHelper::whatsappDisplay() }}</a></li>
                        <li>📱 WhatsApp 2: <a href="{{ \App\Helpers\ContactHelper::whatsappLink2() }}" class="text-toba-green font-semibold hover:underline">{{ \App\Helpers\ContactHelper::whatsappDisplay2() }}</a></li>
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
