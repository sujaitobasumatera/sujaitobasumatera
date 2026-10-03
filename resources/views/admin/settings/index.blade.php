@extends('admin.layout')

@section('title', 'Pengaturan Umum')
@section('page-title', 'Pengaturan Umum')

@section('content')
<div x-data="{ activeTab: 'branding', exchangeRateType: '{{ $general['finance']['exchange_rate_type'] ?? 'manual' }}' }" class="space-y-8">

    <div class="bg-white p-2 rounded-[2rem] shadow-sm border border-slate-100 flex items-center gap-1 max-w-full overflow-x-auto no-scrollbar">
        <button @click="activeTab = 'branding'" :class="activeTab === 'branding' ? 'bg-slate-900 text-white shadow-lg' : 'text-slate-400 hover:bg-slate-50'" class="shrink-0 px-4 md:px-8 py-3 rounded-[1.2rem] font-black text-[10px] uppercase tracking-widest transition whitespace-nowrap">
            <i class="fas fa-palette mr-2"></i> Branding
        </button>
        <button @click="activeTab = 'contact'" :class="activeTab === 'contact' ? 'bg-slate-900 text-white shadow-lg' : 'text-slate-400 hover:bg-slate-50'" class="shrink-0 px-4 md:px-8 py-3 rounded-[1.2rem] font-black text-[10px] uppercase tracking-widest transition whitespace-nowrap">
            <i class="fas fa-address-book mr-2"></i> Kontak & WA
        </button>
        <button @click="activeTab = 'seo'" :class="activeTab === 'seo' ? 'bg-slate-900 text-white shadow-lg' : 'text-slate-400 hover:bg-slate-50'" class="shrink-0 px-4 md:px-8 py-3 rounded-[1.2rem] font-black text-[10px] uppercase tracking-widest transition whitespace-nowrap">
            <i class="fas fa-search mr-2"></i> SEO Global
        </button>
        <button @click="activeTab = 'company'" :class="activeTab === 'company' ? 'bg-slate-900 text-white shadow-lg' : 'text-slate-400 hover:bg-slate-50'" class="shrink-0 px-4 md:px-8 py-3 rounded-[1.2rem] font-black text-[10px] uppercase tracking-widest transition whitespace-nowrap">
            <i class="fas fa-building mr-2"></i> Perusahaan & Invoice
        </button>
        <button @click="activeTab = 'finance'" :class="activeTab === 'finance' ? 'bg-slate-900 text-white shadow-lg' : 'text-slate-400 hover:bg-slate-50'" class="shrink-0 px-4 md:px-8 py-3 rounded-[1.2rem] font-black text-[10px] uppercase tracking-widest transition whitespace-nowrap">
            <i class="fas fa-coins mr-2"></i> Keuangan & Pajak
        </button>
    </div>

    <form action="{{ route('admin.settings.general.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8 pb-14">
        @csrf
        
        <!-- Branding Tab -->
        <div x-show="activeTab === 'branding'" x-transition class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <div class="xl:col-span-2 space-y-6">
                <div class="bg-white rounded-[3rem] p-10 border border-slate-100 shadow-sm space-y-8">
                    <h3 class="text-xl font-black text-slate-900 flex items-center gap-3">
                        <span class="w-2 h-8 bg-toba-green rounded-full"></span> Identitas Visual
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-4">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Logo Utama (Light Mode)</label>
                            <div class="relative group h-32 rounded-3xl bg-slate-50 border-2 border-dashed border-slate-200 overflow-hidden flex flex-col items-center justify-center p-4">
                                <img src="{{ $general['logo_light_url'] ?? asset('assets/img/logo.png') }}" class="max-h-full object-contain" id="preview-logo-light">
                                <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-2">
                                    <button type="button" @click="openMedia('logo_light')" class="w-10 h-10 rounded-full bg-slate-800 text-white flex items-center justify-center hover:bg-green-700 transition"><i class="fas fa-images"></i></button>
                                </div>
                                <input type="hidden" name="logo_light_url" value="{{ $general['logo_light_url'] ?? '' }}">
                            </div>
                        </div>

                        <div class="space-y-4">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Logo Footer (Dark Mode)</label>
                            <div class="relative group h-32 rounded-3xl bg-slate-900 border-2 border-dashed border-slate-700 overflow-hidden flex flex-col items-center justify-center p-4">
                                <img src="{{ $general['logo_dark_url'] ?? asset('assets/img/logo-white.png') }}" class="max-h-full object-contain" id="preview-logo-dark">
                                <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-2">
                                    <button type="button" @click="openMedia('logo_dark')" class="w-10 h-10 rounded-full bg-slate-800 text-white flex items-center justify-center hover:bg-green-700 transition"><i class="fas fa-images"></i></button>
                                </div>
                                <input type="hidden" name="logo_dark_url" value="{{ $general['logo_dark_url'] ?? '' }}">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-4">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Site Favicon</label>
                            <div class="flex items-center gap-6 p-6 bg-slate-50 rounded-3xl border border-slate-100">
                                <div class="w-16 h-16 bg-white rounded-2xl shadow-sm flex items-center justify-center overflow-hidden border border-slate-200">
                                    <img src="{{ $general['icon_url'] ?? asset('favicon.ico') }}" class="w-8 h-8 object-contain" id="preview-favicon">
                                </div>
                                <div class="flex-1 space-y-2">
                                    <p class="text-[8px] text-slate-400 leading-relaxed font-bold uppercase tracking-tighter">Format .ico atau .png (64x64px)</p>
                                    <input type="hidden" name="icon_url" value="{{ $general['icon_url'] ?? '' }}">
                                </div>
                                <button type="button" @click="openMedia('favicon')" class="w-10 h-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-green-700 transition shadow-sm">
                                    <i class="fas fa-images text-xs"></i>
                                </button>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Nama Situs</label>
                            <input type="text" name="site_name" value="{{ $general['site_name'] ?? 'Sujai Laketoba' }}" class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-toba-green font-bold text-slate-900">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="space-y-6">
                <div class="bg-white rounded-[3rem] p-8 border border-slate-100 shadow-sm">
                    <h4 class="text-sm font-black text-slate-900 mb-6 flex items-center gap-2">
                        <i class="fas fa-info-circle text-toba-green"></i> Informasi Tambahan
                    </h4>
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Slogan Situs</label>
                            <input type="text" name="site_tagline" value="{{ $general['site_tagline'] ?? 'Temukan Keindahan Danau Toba Bersama Kami' }}" class="w-full px-5 py-3 bg-slate-50 border-none rounded-xl font-bold text-xs text-slate-700">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Deskripsi Singkat Footer</label>
                            <textarea name="site_footer_desc" rows="4" class="w-full px-5 py-3 bg-slate-50 border-none rounded-xl font-bold text-xs text-slate-500 leading-relaxed">{{ $general['site_footer_desc'] ?? 'Platform tour & travel terpercaya untuk eksplorasi Danau Toba dan sekitarnya.' }}</textarea>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Teks Copyright Footer</label>
                            <input type="text" name="site_copyright" value="{{ $general['site_copyright'] ?? '' }}" placeholder="Sujai Laketoba" class="w-full px-5 py-3 bg-slate-50 border-none rounded-xl font-bold text-xs text-slate-700">
                            <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest italic">Tahun & "All rights reserved" otomatis ditambahkan.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Tab -->
        <div x-show="activeTab === 'contact'" x-transition class="space-y-8">
            <div class="bg-white rounded-[3.5rem] p-12 border border-slate-100 shadow-sm max-w-4xl mx-auto space-y-6">
                <h3 class="text-xl font-black text-slate-900 flex items-center gap-3">
                    <span class="w-2 h-8 bg-green-500 rounded-full"></span> Konfigurasi Kontak & WA
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <div class="space-y-6">
                        <div class="p-8 bg-green-50 rounded-[2.5rem] space-y-4">
                            <div class="flex items-center gap-4 text-green-600">
                                <i class="fab fa-whatsapp text-3xl"></i>
                                <span class="text-[10px] font-black uppercase tracking-widest">WhatsApp Main Number</span>
                            </div>
                            <input type="text" name="contact_whatsapp" value="{{ $general['contact_whatsapp'] ?? '628123456789' }}" class="w-full px-6 py-4 bg-white border-none rounded-2xl font-black text-slate-900 text-lg shadow-sm">
                            <p class="text-[8px] font-bold text-green-400 uppercase tracking-widest italic">Mulai dengan kode negara (misal: 62813...)</p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">WA Welcome Message</label>
                            <input type="text" name="wa_message" value="{{ $general['wa_message'] ?? 'Halo Sujai Laketoba, saya ingin bertanya tentang...' }}" class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl font-bold text-slate-700 text-xs">
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="space-y-4">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <i class="fas fa-envelope text-slate-300"></i> Email Kontak
                            </label>
                            <input type="email" name="contact_email" value="{{ $general['contact_email'] ?? 'hello@sujaitobasumatera.com' }}" class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl font-bold text-slate-900">
                        </div>
                        <div class="space-y-4">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <i class="fas fa-location-dot text-slate-300"></i> Alamat Kantor Utama
                            </label>
                            <textarea name="office_address" rows="3" class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl font-bold text-slate-600 text-xs leading-relaxed">{{ $general['office_address'] ?? 'Jl. Sipoholon No. 45, Balige, Toba, Sumatera Utara.' }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="pt-8 border-t border-slate-100 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2"><i class="fas fa-phone text-slate-300"></i> Nomor Telepon (Non-WA)</label>
                        <input type="text" name="contact_phone" value="{{ $general['contact_phone'] ?? '' }}" placeholder="0632 21234" class="w-full px-5 py-3 bg-slate-50 border-none rounded-xl font-bold text-xs text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2"><i class="fab fa-whatsapp text-green-400"></i> WhatsApp Kedua (CS Cadangan)</label>
                        <input type="text" name="contact_whatsapp_2" value="{{ $general['contact_whatsapp_2'] ?? '' }}" placeholder="62813..." class="w-full px-5 py-3 bg-slate-50 border-none rounded-xl font-bold text-xs text-slate-700">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2"><i class="fas fa-clock text-slate-300"></i> Jam Operasional</label>
                        <input type="text" name="operating_hours" value="{{ $general['operating_hours'] ?? '' }}" placeholder="Setiap hari, 08.00 - 21.00 WIB" class="w-full px-5 py-3 bg-slate-50 border-none rounded-xl font-bold text-xs text-slate-700">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Kota</label>
                            <input type="text" name="office_city" value="{{ $general['office_city'] ?? '' }}" placeholder="Balige" class="w-full px-5 py-3 bg-slate-50 border-none rounded-xl font-bold text-xs text-slate-700">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Kode Pos</label>
                            <input type="text" name="office_postal" value="{{ $general['office_postal'] ?? '' }}" placeholder="22312" class="w-full px-5 py-3 bg-slate-50 border-none rounded-xl font-bold text-xs text-slate-700">
                        </div>
                    </div>
                    <div class="md:col-span-2 space-y-2">
                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2"><i class="fas fa-map text-slate-300"></i> Embed Google Maps <span class="text-slate-300 normal-case">(opsional — tempel URL "src" dari kode embed)</span></label>
                        <input type="text" name="google_maps_embed" value="{{ $general['google_maps_embed'] ?? '' }}" placeholder="https://www.google.com/maps/embed?pb=..." class="w-full px-5 py-3 bg-slate-50 border-none rounded-xl font-bold text-[10px] text-slate-700">
                    </div>
                </div>

                <div class="pt-8 border-t border-slate-100 grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div class="space-y-2">
                        <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Instagram</label>
                        <input type="text" name="social_instagram" value="{{ $general['social_instagram'] ?? '' }}" placeholder="@sujaitobasumatera" class="w-full px-4 py-2.5 bg-slate-50 border-none rounded-xl font-bold text-[10px]">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Facebook</label>
                        <input type="text" name="social_facebook" value="{{ $general['social_facebook'] ?? '' }}" class="w-full px-4 py-2.5 bg-slate-50 border-none rounded-xl font-bold text-[10px]">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest">TikTok</label>
                        <input type="text" name="social_tiktok" value="{{ $general['social_tiktok'] ?? '' }}" class="w-full px-4 py-2.5 bg-slate-50 border-none rounded-xl font-bold text-[10px]">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Youtube</label>
                        <input type="text" name="social_youtube" value="{{ $general['social_youtube'] ?? '' }}" class="w-full px-4 py-2.5 bg-slate-50 border-none rounded-xl font-bold text-[10px]">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest">X / Twitter</label>
                        <input type="text" name="social_twitter" value="{{ $general['social_twitter'] ?? '' }}" placeholder="https://x.com/..." class="w-full px-4 py-2.5 bg-slate-50 border-none rounded-xl font-bold text-[10px]">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest">LinkedIn</label>
                        <input type="text" name="social_linkedin" value="{{ $general['social_linkedin'] ?? '' }}" placeholder="https://linkedin.com/company/..." class="w-full px-4 py-2.5 bg-slate-50 border-none rounded-xl font-bold text-[10px]">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Telegram</label>
                        <input type="text" name="social_telegram" value="{{ $general['social_telegram'] ?? '' }}" placeholder="https://t.me/..." class="w-full px-4 py-2.5 bg-slate-50 border-none rounded-xl font-bold text-[10px]">
                    </div>
                </div>

                <!-- Rating & Ulasan (angka dari Google Maps, diisi manual) -->
                <div class="pt-8 border-t border-slate-100 space-y-6">
                    <div class="flex items-center gap-3">
                        <span class="text-amber-400 text-lg">★</span>
                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-500">Rating & Ulasan</span>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Rating <span class="text-slate-300 normal-case">(0–5)</span></label>
                            <input type="number" step="0.1" min="0" max="5" name="rating_override" value="{{ $general['rating_override'] ?? '' }}" placeholder="mis. 4.9" class="w-full px-4 py-2.5 bg-slate-50 border-none rounded-xl font-bold text-[10px]">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Jumlah Ulasan</label>
                            <input type="number" min="0" name="review_count_override" value="{{ $general['review_count_override'] ?? '' }}" placeholder="mis. 87" class="w-full px-4 py-2.5 bg-slate-50 border-none rounded-xl font-bold text-[10px]">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Link Google Maps <span class="text-slate-300 normal-case">(opsional — jumlah ulasan jadi link ke listing)</span></label>
                        <input type="text" name="google_maps_url" value="{{ $general['google_maps_url'] ?? '' }}" placeholder="https://maps.app.goo.gl/..." class="w-full px-4 py-2.5 bg-slate-50 border-none rounded-xl font-bold text-[10px]">
                    </div>

                    <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest italic leading-relaxed">
                        Isi sesuai angka yang tampil di Google Maps Anda. Kosongkan rating → badge disembunyikan (bukan angka palsu).
                    </p>
                </div>
            </div>
        </div>

        <!-- SEO Tab -->
        <div x-show="activeTab === 'seo'" x-transition class="space-y-8">
            <div class="bg-white rounded-[3.5rem] p-12 border border-slate-100 shadow-sm max-w-4xl mx-auto space-y-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-black text-slate-900 flex items-center gap-3">
                        <span class="w-2 h-8 bg-green-700 rounded-full"></span> Global SEO & Indexing
                    </h3>
                    <div class="flex items-center gap-2 bg-green-100 px-4 py-2 rounded-full">
                        <div class="w-2 h-2 rounded-full bg-green-700 animate-pulse"></div>
                        <span class="text-[8px] font-black text-green-800 uppercase tracking-widest">Live Optimization</span>
                    </div>
                </div>

                <div class="space-y-8">
                    <div class="space-y-3">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Meta Title Global (Brand)</label>
                        <input type="text" name="seo_meta_title" value="{{ $general['seo_meta_title'] ?? 'Sujai Laketoba - Tour & Travel Agency' }}" class="w-full px-8 py-5 bg-slate-50 border-none rounded-3xl font-black text-slate-900 text-lg shadow-inner">
                        <p class="text-[9px] text-slate-400 font-bold italic">Rekomendasi: 50-60 karakter.</p>
                    </div>

                    <div class="space-y-3">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Meta Description Global</label>
                        <textarea name="seo_meta_desc" rows="4" class="w-full px-8 py-6 bg-slate-50 border-none rounded-3xl font-bold text-slate-600 text-sm leading-relaxed">{{ $general['seo_meta_desc'] ?? 'Agen perjalanan terpercaya untuk wisata Danau Toba, Samosir, dan Sumatera Utara. Paket tour lengkap dengan akomodasi terbaik.' }}</textarea>
                        <p class="text-[9px] text-slate-400 font-bold italic">Rekomendasi: 150-160 karakter.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-4">
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Global Meta Keywords</label>
                            <textarea name="seo_meta_keywords" rows="3" class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl font-bold text-slate-500 text-xs">{{ $general['seo_meta_keywords'] ?? 'danau toba, tour samosir, travel medan, paket wisata sumatera utara' }}</textarea>
                        </div>
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-green-700 uppercase tracking-widest">Daftar Kota Target SEO (pSEO Origins)</label>
                            <textarea name="seo_pseo_origins" rows="3" placeholder="Jakarta, Surabaya, Malaysia, Singapore" class="w-full px-6 py-4 bg-green-100/50 border-none rounded-2xl font-bold text-green-950 text-xs">{{ $general['seo_pseo_origins'] ?? 'Jakarta, Surabaya, Bandung, Bali, Batam, Palembang, Makassar, Semarang, Yogyakarta, Kuala Lumpur, Singapore, Penang, Pekanbaru, Padang, Malaysia' }}</textarea>
                            <p class="text-[8px] font-bold text-slate-400 uppercase">Pisahkan dengan koma. Akan otomatis membuat halaman target SEO & masuk ke Sitemap.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-4">
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Google Analytics / GTM ID</label>
                            <input type="text" name="seo_ga_id" value="{{ $general['seo_ga_id'] ?? '' }}" placeholder="G-XXXXXXXXXX" class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl font-black text-slate-400 text-xs tracking-widest">
                            <p class="text-[8px] font-bold text-slate-400 uppercase">Input ID saja, skrip akan ditambahkan otomatis.</p>
                        </div>
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2"><i class="fab fa-google text-rose-400"></i> Google Search Console</label>
                            <input type="text" name="seo_google_verification" value="{{ $general['seo_google_verification'] ?? '' }}" placeholder="kode verifikasi (content dari meta tag)" class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl font-black text-slate-400 text-xs tracking-widest">
                            <p class="text-[8px] font-bold text-slate-400 uppercase">Tempel hanya nilai "content" dari meta google-site-verification.</p>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 space-y-3">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2"><i class="fas fa-share-nodes text-green-600"></i> Gambar Share Default (og:image)</label>
                        <p class="text-[9px] text-slate-400 font-bold italic">Tampil saat link situs dibagikan di WhatsApp / Facebook (untuk halaman tanpa gambar khusus). Rekomendasi 1200×630px.</p>
                        <div class="flex items-center gap-6 p-6 bg-slate-50 rounded-3xl border border-slate-100">
                            <div class="w-32 h-20 bg-white rounded-2xl shadow-sm flex items-center justify-center overflow-hidden border border-slate-200 shrink-0">
                                <img src="{{ $general['og_image_url'] ?? asset('assets/img/logo.png') }}" class="w-full h-full object-contain" id="preview-og-image">
                            </div>
                            <div class="flex-1">
                                <input type="hidden" name="og_image_url" value="{{ $general['og_image_url'] ?? '' }}">
                                <button type="button" @click="openMedia('og_image')" class="px-5 py-3 bg-white border border-slate-200 rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-500 hover:text-green-700 transition shadow-sm">
                                    <i class="fas fa-images mr-1"></i> Pilih dari Media
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-4 border-t border-slate-100">
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <i class="fab fa-facebook-square text-blue-500"></i> Meta (Facebook) Pixel ID
                            </label>
                            <input type="text" name="seo_pixel_id" value="{{ $general['seo_pixel_id'] ?? '' }}" placeholder="123456789012345" class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl font-black text-slate-400 text-xs tracking-widest">
                            <p class="text-[8px] font-bold text-slate-400 uppercase">Masukkan Pixel ID untuk retargeting Facebook/Instagram Ads.</p>
                        </div>
                        <div class="space-y-3 flex flex-col justify-center p-6 bg-blue-50 rounded-2xl">
                            <p class="text-[9px] font-black text-blue-500 uppercase tracking-widest flex items-center gap-2"><i class="fas fa-lightbulb"></i> Tips Retargeting</p>
                            <p class="text-[10px] text-blue-400 leading-relaxed">Dengan Pixel aktif, Anda bisa menargetkan ulang pengunjung yang sudah melihat halaman paket wisata Anda di Facebook & Instagram Ads.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Company & Invoice Tab -->
        @php $company = $company ?? []; @endphp
        <div x-show="activeTab === 'company'" x-transition class="space-y-8">
            <div class="bg-white rounded-[3.5rem] p-12 border border-slate-100 shadow-sm max-w-4xl mx-auto space-y-6">
                <div>
                    <h3 class="text-xl font-black text-slate-900 flex items-center gap-3">
                        <span class="w-2 h-8 bg-amber-500 rounded-full"></span> Identitas Perusahaan & Invoice
                    </h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-3 ml-5">Data ini muncul di header & instruksi pembayaran pada PDF invoice.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <div class="space-y-6">
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <i class="fas fa-id-card-clip text-slate-300"></i> Nama Legal Perusahaan
                            </label>
                            <input type="text" name="company[legal_name]" value="{{ $company['legal_name'] ?? '' }}" placeholder="PT Sujai Laketoba Experience" class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl font-bold text-slate-900">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <i class="fas fa-receipt text-slate-300"></i> NPWP
                            </label>
                            <input type="text" name="company[tax_id]" value="{{ $company['tax_id'] ?? '' }}" placeholder="00.000.000.0-000.000" class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl font-bold text-slate-700">
                            <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest italic">Kosongkan bila tidak ingin ditampilkan di invoice.</p>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="p-8 bg-amber-50 rounded-[2.5rem] space-y-4">
                            <div class="flex items-center gap-4 text-amber-600">
                                <i class="fas fa-building-columns text-2xl"></i>
                                <span class="text-[10px] font-black uppercase tracking-widest">Rekening Pembayaran</span>
                            </div>
                            <div class="space-y-2">
                                <label class="text-[9px] font-black text-amber-500/70 uppercase tracking-widest">Bank & Nomor Rekening</label>
                                <input type="text" name="company[bank_account]" value="{{ $company['bank_account'] ?? '' }}" placeholder="BCA 1234567890" class="w-full px-6 py-4 bg-white border-none rounded-2xl font-black text-slate-900 shadow-sm">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[9px] font-black text-amber-500/70 uppercase tracking-widest">Atas Nama</label>
                                <input type="text" name="company[bank_account_name]" value="{{ $company['bank_account_name'] ?? '' }}" placeholder="PT Sujai Laketoba Experience" class="w-full px-6 py-4 bg-white border-none rounded-2xl font-bold text-slate-700 shadow-sm">
                            </div>
                            <p class="text-[8px] font-bold text-amber-400 uppercase tracking-widest italic">Instruksi transfer hanya muncul bila nomor rekening diisi.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Finance Tab -->
        @php $finance = $general['finance'] ?? []; @endphp
        <div x-show="activeTab === 'finance'" x-transition class="space-y-8">
            <div class="bg-white rounded-[3.5rem] p-12 border border-slate-100 shadow-sm max-w-4xl mx-auto space-y-6">
                <h3 class="text-xl font-black text-slate-900 flex items-center gap-3">
                    <span class="w-2 h-8 bg-blue-500 rounded-full"></span> Pengaturan Keuangan
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                    <div class="space-y-6">
                        <div class="p-8 bg-blue-50 rounded-[2.5rem] space-y-4">
                            <div class="flex items-center gap-4 text-blue-600">
                                <i class="fas fa-percent text-3xl"></i>
                                <span class="text-[10px] font-black uppercase tracking-widest">Pajak & Layanan</span>
                            </div>
                            <div class="flex items-center gap-2">
                                {{-- Bawaan 0. Angka 11 yang tertanam sebelumnya
                                     membuat setiap pesanan dipungut PPN tanpa
                                     seorang pun pernah memilihnya. Isi hanya
                                     bila memang sudah berstatus PKP. --}}
                                <input type="number" step="0.1" name="finance[tax_percentage]" value="{{ $finance['tax_percentage'] ?? 0 }}" class="w-32 px-4 py-4 bg-white border-none rounded-2xl font-black text-slate-900 text-lg shadow-sm text-center">
                                <span class="font-bold text-slate-500 text-xl">%</span>
                            </div>
                            <p class="text-[8px] font-bold text-blue-400 uppercase tracking-widest italic">Pajak akan ditambahkan di total akhir pemesanan paket.</p>
                        </div>
                    </div>

                        <div class="space-y-4">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <i class="fas fa-key text-slate-300"></i> API Key (ExchangeRate-API)
                            </label>
                            <input type="text" name="finance[exchange_rate_api_key]" id="api_key_input" value="{{ $finance['exchange_rate_api_key'] ?? '' }}" placeholder="Tempel API Key ExchangeRate-API" class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl font-bold text-slate-900">
                            <div class="flex items-center justify-between mt-2">
                                <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest italic">Ambil dari app.exchangerate-api.com.</p>
                                <button type="button" onclick="refreshRates()" class="text-[9px] font-bold px-4 py-2 bg-blue-100 text-blue-600 rounded-lg hover:bg-blue-200 transition">
                                    <i class="fas fa-sync-alt mr-1"></i> Refresh Kurs dari API
                                </button>
                            </div>
                        </div>

                        <!-- Peak Season & Surcharge -->
                        <div class="p-8 bg-amber-50 rounded-[2.5rem] space-y-4 mt-6">
                            <div class="flex items-center gap-4 text-amber-600">
                                <i class="fas fa-chart-line text-3xl"></i>
                                <span class="text-[10px] font-black uppercase tracking-widest">Harga Dinamis (Surcharge)</span>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label class="text-[9px] font-black text-amber-500/70 uppercase tracking-widest">Weekend Surcharge (%)</label>
                                    <input type="number" step="1" name="finance[surcharge_weekend]" value="{{ $finance['surcharge_weekend'] ?? 0 }}" placeholder="mis. 5" class="w-full px-6 py-4 bg-white border-none rounded-2xl font-black text-slate-900 shadow-sm">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[9px] font-black text-amber-500/70 uppercase tracking-widest">Peak Season Surcharge (%)</label>
                                    <input type="number" step="1" name="finance[surcharge_peak]" value="{{ $finance['surcharge_peak'] ?? 0 }}" placeholder="mis. 15" class="w-full px-6 py-4 bg-white border-none rounded-2xl font-black text-slate-900 shadow-sm">
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label class="text-[9px] font-black text-amber-500/70 uppercase tracking-widest">Mulai Peak Season (Tgl/Bln)</label>
                                    <input type="text" name="finance[surcharge_peak_start]" value="{{ $finance['surcharge_peak_start'] ?? '' }}" placeholder="DD/MM (mis. 20/12)" class="w-full px-6 py-4 bg-white border-none rounded-2xl font-bold text-slate-900 shadow-sm">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-[9px] font-black text-amber-500/70 uppercase tracking-widest">Akhir Peak Season (Tgl/Bln)</label>
                                    <input type="text" name="finance[surcharge_peak_end]" value="{{ $finance['surcharge_peak_end'] ?? '' }}" placeholder="DD/MM (mis. 05/01)" class="w-full px-6 py-4 bg-white border-none rounded-2xl font-bold text-slate-900 shadow-sm">
                                </div>
                            </div>
                            <p class="text-[8px] font-bold text-amber-400 uppercase tracking-widest italic">Otomatis menaikkan harga pada hari Sabtu-Minggu, atau pada rentang tanggal Peak Season.</p>
                        </div>

                        <div class="space-y-4 mt-6">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2">
                                <i class="fas fa-coins text-slate-300"></i> Nilai Kurs MYR (1 MYR =)
                            </label>
                            <div class="flex items-center mb-2">
                                <span class="px-4 py-4 bg-slate-100 rounded-l-2xl font-bold text-slate-500">Rp</span>
                                <input type="number" name="finance[exchange_rate_manual_myr]" id="rate_myr_input" value="{{ $finance['exchange_rate_manual_myr'] ?? \App\Helpers\CurrencyHelper::DEFAULT_MYR_IDR }}" class="w-full px-6 py-4 bg-slate-50 border-none rounded-r-2xl font-bold text-slate-900">
                            </div>
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest flex items-center gap-2 mt-4">
                                <i class="fas fa-coins text-slate-300"></i> Nilai Kurs SGD (1 SGD =)
                            </label>
                            <div class="flex items-center">
                                <span class="px-4 py-4 bg-slate-100 rounded-l-2xl font-bold text-slate-500">Rp</span>
                                <input type="number" name="finance[exchange_rate_manual_sgd]" id="rate_sgd_input" value="{{ $finance['exchange_rate_manual_sgd'] ?? \App\Helpers\CurrencyHelper::DEFAULT_SGD_IDR }}" class="w-full px-6 py-4 bg-slate-50 border-none rounded-r-2xl font-bold text-slate-900">
                            </div>
                            <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mt-2 italic">Nilai ini yang akan digunakan di website. Bisa diedit manual atau klik Refresh Kurs.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fixed Save Button -->
        <div class="fixed bottom-[calc(1.5rem+env(safe-area-inset-bottom))] right-4 md:bottom-10 md:right-10 z-[100]">
            <button type="submit" class="group flex items-center gap-4 bg-slate-900 text-white px-8 py-5 rounded-full font-black text-[10px] uppercase tracking-widest shadow-2xl hover:bg-toba-green hover:scale-105 transition duration-300">
                <i class="fas fa-save text-lg group-hover:rotate-12 transition-transform"></i>
                Simpan Semua Perubahan
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function previewImage(input, previewId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById(previewId).src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function openMedia(target) {
        window.dispatchEvent(new CustomEvent('open-media-picker', { 
            detail: { 
                callback: (item) => {
                    const url = '/storage/' + item.path;
                    if (target === 'logo_light') {
                        document.getElementById('preview-logo-light').src = url;
                        document.querySelector('input[name="logo_light_url"]').value = url;
                    } else if (target === 'logo_dark') {
                        document.getElementById('preview-logo-dark').src = url;
                        document.querySelector('input[name="logo_dark_url"]').value = url;
                    } else if (target === 'favicon') {
                        document.getElementById('preview-favicon').src = url;
                        document.querySelector('input[name="icon_url"]').value = url;
                    } else if (target === 'og_image') {
                        document.getElementById('preview-og-image').src = url;
                        document.querySelector('input[name="og_image_url"]').value = url;
                    }
                } 
            } 
        }));
    }
    function refreshRates() {
        if (confirm("Sistem akan mengambil kurs terbaru dari API. Pastikan Anda telah menyimpan API Key bila baru saja mengubahnya. Lanjutkan?")) {
            const btn = event.currentTarget || document.querySelector('button[onclick="refreshRates()"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Memuat...';
            btn.disabled = true;
            
            fetch('{{ route('admin.settings.refresh-rates') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                
                if (data.error) {
                    alert('Gagal! ' + data.error);
                } else {
                    document.getElementById('rate_myr_input').value = data.MYR;
                    document.getElementById('rate_sgd_input').value = data.SGD;
                    alert('Berhasil! Kurs MYR dan SGD telah diperbarui. Jangan lupa klik Simpan Pengaturan di kanan bawah.');
                }
            })
            .catch(error => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                alert('Gagal! Terjadi kesalahan sistem saat mengambil data.');
            });
        }
    }
</script>
@endpush
@endsection
