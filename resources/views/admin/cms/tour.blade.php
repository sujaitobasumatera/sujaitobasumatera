@extends('admin.layout')

@section('title', 'CMS Beranda Tour')
@section('page-title', 'CMS Beranda Tour')

@section('content')
@php
    $resolve = function($path, $default = '') {
        return imageUrl($path, $default);
    };
@endphp

    @php
        $slides = $settings['homepage_slides'] ?? [];
        foreach ($slides as &$slide) {
            if (!empty($slide['image_url'])) {
                $slide['image_url'] = $resolve($slide['image_url']);
            }
        }
    @endphp

<div x-cloak x-data="cmsTourHandler" class="flex flex-col xl:flex-row gap-8 min-h-[85vh]">

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('cmsTourHandler', () => ({
        activeTab: 'slider',
        heroTitle: @json($settings['hero_title'] ?? 'Liburan Sempurna di Sumatera Utara.'),
        heroSubtitle: @json($settings['hero_subtitle'] ?? 'Kami mengatur seluruh perjalanan Anda. Nikmati udara pagi Berastagi dan keindahan Samosir tanpa perlu pusing menyusun itinerary.'),
        heroImage: @json($resolve($settings['hero_image_url'] ?? '', 'sumatra-panorama')),
        ctaText: @json($settings['hero_cta_text'] ?? 'Lihat Paket Wisata'),
        
        slides: @json($slides),
        activeSlideIdx: 0,
        
        addSlide() {
            this.slides.push({
                type: 'manual',
                title: 'Slide Baru',
                subtitle: 'Deskripsi slide baru Anda di sini.',
                image_url: '',
                cta_text: 'Eksplor Sekarang',
                cta_link: '#',
                location: 'Sumatera Utara',
                price: '0',
                duration: '1 Hari'
            });
            this.activeSlideIdx = this.slides.length - 1;
        },
        
        removeSlide(idx) {
            if (confirm('Hapus slide ini?')) {
                this.slides.splice(idx, 1);
                if (this.activeSlideIdx >= this.slides.length) {
                    this.activeSlideIdx = Math.max(0, this.slides.length - 1);
                }
            }
        },

        importFromPackage(pkg) {
            const currentSlide = this.slides[this.activeSlideIdx];
            this.slides[this.activeSlideIdx] = {
                ...currentSlide,
                type: 'package',
                id: pkg.id,
                title: pkg.name,
                subtitle: pkg.shortDescription,
                image_url: pkg.image_path || '',
                cta_link: '/tour/package/' + (pkg.slug || pkg.id),
                location: pkg.locationTag || 'Sumatera Utara',
                price: pkg.price ? pkg.price.toString() : '0',
                duration: pkg.duration || ''
            };
        },

        importFromBlog(blog) {
            this.slides[this.activeSlideIdx] = {
                ...this.slides[this.activeSlideIdx],
                type: 'blog',
                id: blog.id,
                title: blog.title,
                subtitle: blog.shortDescription,
                image_url: blog.thumbnail,
                cta_link: '/tour/blog/' + (blog.slug || blog.id),
                location: 'Blog Post',
                price: '0',
                duration: ''
            };
        },

        importFromGallery(img) {
            this.slides[this.activeSlideIdx] = {
                ...this.slides[this.activeSlideIdx],
                type: 'gallery',
                id: img.id,
                title: img.title || 'Sujai Toba Sumatera Gallery',
                subtitle: img.description || '',
                image_url: img.image_path,
                cta_link: '#',
                location: 'Gallery',
                price: '0',
                duration: ''
            };
        },

        handleSlideImage(e, index) {
            const file = e.target.files[0];
            if (file) {
                this.slides[index].image_url = URL.createObjectURL(file);
            }
        },
        
        stat0: @json($settings['stat_value_0'] ?? '1.5K+'),
        label0: @json($settings['stat_label_0'] ?? 'Liburan Sukses'),
        stat1: @json($settings['stat_value_1'] ?? '10K+'),
        label1: @json($settings['stat_label_1'] ?? 'Wisatawan'),
        stat2: @json($settings['stat_value_2'] ?? '50+'),
        label2: @json($settings['stat_label_2'] ?? 'Destinasi'),
        stat3: @json($settings['stat_value_3'] ?? '15+'),
        label3: @json($settings['stat_label_3'] ?? 'Penghargaan'),
        
        fixPath(path) {
            if (!path) return '';
            let lower = path.toLowerCase();
            
            // Map keywords to local paths
            if (lower.includes('staff1')) return '/images/sumut/specialist_avatar.webp';
            if (lower.includes('user1')) return '/images/sumut/avatar_user_1.webp';
            if (lower.includes('user2')) return '/images/sumut/avatar_user_2.webp';
            if (lower.includes('user3')) return '/images/sumut/avatar_user_3.webp';
            if (lower.includes('user4')) return '/images/sumut/avatar_user_4.webp';
            if (lower.includes('outbound')) return '/images/home/outbound.webp';
            if (lower.includes('tour')) return '/images/home/tour.webp';
            
            // Legacy DB paths
            if (lower.includes('2023/10/')) {
                if (lower.includes('001-1.jpg')) return '/images/sumut/toba_hero.webp';
                if (lower.includes('002-1.jpg')) return '/images/sumut/toba_landscape.webp';
                if (lower.includes('003-1.jpg')) return '/images/sumut/batak_house.webp';
                if (lower.includes('004.jpg')) return '/images/sumut/sipiso_piso.webp';
                if (lower.includes('005.jpg')) return '/images/sumut/berastagi.webp';
                if (lower.includes('006.jpg')) return '/images/sumut/lumbini.webp';
                if (lower.includes('008.jpg')) return '/images/sumut/hotel_room.webp';
                if (lower.includes('009-1.jpg')) return '/images/sumut/maimun_palace.webp';
                if (lower.includes('0010.jpg') || lower.includes('010.jpg')) return '/images/sumut/masjid_raya.webp';
                if (lower.includes('team-building') || lower.includes('fun-games') || lower.includes('gathering') || lower.includes('outbound-kids')) {
                    return '/images/home/outbound.webp';
                }
            }

            // Remote URLs
            if (lower.includes('unsplash.com') || lower.includes('placeholder') || lower.includes('pravatar.cc') || lower.includes('googleusercontent.com')) {
                if (lower.includes('photo-1580489944761') || lower.includes('staff1')) return '/images/sumut/specialist_avatar.webp';
                if (lower.includes('photo-1507003211169') || lower.includes('user1') || lower.includes('ab6axubc2hfgasrsa7a85bf12siuk3')) return '/images/sumut/avatar_user_1.webp';
                if (lower.includes('photo-1534528741775') || lower.includes('user2') || lower.includes('ab6axuafawoa9yazv80gupi35ev08b')) return '/images/sumut/avatar_user_2.webp';
                if (lower.includes('photo-1500648767791') || lower.includes('user3')) return '/images/sumut/avatar_user_3.webp';
                if (lower.includes('photo-1494790108377') || lower.includes('user4')) return '/images/sumut/avatar_user_4.webp';
                if (lower.includes('photo-1472099645785')) return '/images/sumut/avatar_user_1.webp';
                if (lower.includes('photo-1596402184320') || lower.includes('photo-1544735049') || lower.includes('photo-1511632765')) return '/images/sumut/sumatra_panorama.webp';
                if (lower.includes('googleusercontent.com')) return '/images/sumut/avatar_user_3.webp';
                return '/images/home/tour.webp';
            }

            if (path.startsWith('http') || path.startsWith('blob:') || path.startsWith('data:')) return path;
            
            let clean = path.replace(/^\/?storage\//, '').replace(/^\//, '');
            return '/storage/' + clean;
        },

        updatePreview(e) {
            const file = e.target.files[0];
            if (file) {
                this.heroImage = URL.createObjectURL(file);
            }
        },

        openMedia(target, idx = null) {
            window.dispatchEvent(new CustomEvent('open-media-picker', { 
                detail: { 
                    callback: (item) => {
                        this.setMedia(item, target, idx);
                    }
                } 
            }));
        },

        specialist_name: @json($settings['specialist_name'] ?? 'Sarah Anggraini'),
        specialist_wa: @json($settings['specialist_wa'] ?? ''),
        specialist_title: @json($settings['specialist_title'] ?? 'Travel Specialist'),
        specialist_desc: @json($settings['specialist_desc'] ?? 'Punya pertanyaan khusus? Saya siap membantu merencanakan liburan impian Anda.'),
        specialist_image: @json($resolve($settings['specialist_image_url'] ?? '', 'staff1')),
        
        @php
            $defaultTestimonials = [
                ['name' => 'Andini Wijaya', 'location' => 'Jakarta, Indonesia', 'text' => 'Pelayanan sangat profesional. Tour guide ramah dan sangat menguasai medan. Itinerary juga tidak terlalu padat sehingga kami bisa benar-benar menikmati waktu.', 'image' => 'user1'],
                ['name' => 'Budi Santoso', 'location' => 'Surabaya, Indonesia', 'text' => 'Sangat puas dengan pilihan hotel dan restorannya. Sujai Toba Sumatera benar-benar kurasi yang terbaik untuk tamunya. Highly recommended!', 'image' => 'user2']
            ];
        @endphp
        testimonials: (() => {
            let t = @json($settings['testimonials'] ?? $defaultTestimonials);
            while(t.length < 4) t.push({name: '', location: '', text: '', image: ''});
            return t;
        })(),

        addTestimonial() {
            this.testimonials.push({
                name: 'Nama Pengunjung',
                location: 'Kota, Negara',
                text: 'Tulis ulasan pengunjung di sini...',
                image: 'user1'
            });
        },

        removeTestimonial(idx) {
            if (confirm('Hapus ulasan ini?')) {
                this.testimonials.splice(idx, 1);
            }
        },

        setMedia(item, target, idx = null) {
            let path = item.path;
            if (path.startsWith('/storage/')) path = path.replace('/storage/', '');
            if (path.startsWith('storage/')) path = path.replace('storage/', '');
            
            const finalUrl = '/storage/' + path;

            if (target === 'hero') {
                this.heroImage = finalUrl;
                if (this.$refs.heroUrl) this.$refs.heroUrl.value = path;
            } else if (target === 'slider') {
                this.slides[this.activeSlideIdx].image_url = path;
            } else if (target === 'specialist') {
                this.specialist_image = finalUrl;
                document.querySelector('input[name="specialist_image_url"]').value = path;
            } else if (target === 'why_image_1' || target === 'why_image_2' || target === 'why_image_3') {
                const preview = document.getElementById('preview_' + target);
                const empty = document.getElementById('empty_' + target);
                const input = document.getElementById('input_' + target);
                
                if (preview) {
                    preview.src = finalUrl;
                    preview.classList.remove('hidden');
                }
                if (empty) empty.classList.add('hidden');
                if (input) input.value = path;
            } else if (target === 'testimonial' && idx !== null) {
                this.testimonials[idx].image = path;
            }
        }
    }));
});
</script>
@endpush
    
    <!-- LEFT: CONTROL PANEL -->
    <div class="w-full xl:w-[450px] flex-shrink-0 space-y-6">
        <!-- Tab Navigation — scrollable, responsive, tidak tabrakan -->
        <div class="bg-white p-1.5 rounded-2xl shadow-sm border border-slate-100 flex items-center gap-1 overflow-x-auto no-scrollbar" style="scrollbar-width:none;">
            <button type="button" @click="activeTab = 'hero'" :class="activeTab === 'hero' ? 'bg-green-800 text-white shadow' : 'text-slate-500 hover:bg-slate-100'" class="shrink-0 flex items-center gap-1.5 px-3 py-2 rounded-xl font-bold text-[10px] uppercase tracking-wide transition whitespace-nowrap">
                🖼️ <span>Hero</span>
            </button>
            <button type="button" @click="activeTab = 'slider'" :class="activeTab === 'slider' ? 'bg-green-800 text-white shadow' : 'text-slate-500 hover:bg-slate-100'" class="shrink-0 flex items-center gap-1.5 px-3 py-2 rounded-xl font-bold text-[10px] uppercase tracking-wide transition whitespace-nowrap">
                📸 <span>Slider</span>
            </button>
            <button type="button" @click="activeTab = 'featured'" :class="activeTab === 'featured' ? 'bg-amber-500 text-white shadow' : 'text-slate-500 hover:bg-slate-100'" class="shrink-0 flex items-center gap-1.5 px-3 py-2 rounded-xl font-bold text-[10px] uppercase tracking-wide transition whitespace-nowrap">
                ⭐ <span>Featured</span>
            </button>
            <button type="button" @click="activeTab = 'about'" :class="activeTab === 'about' ? 'bg-slate-800 text-white shadow' : 'text-slate-500 hover:bg-slate-100'" class="shrink-0 flex items-center gap-1.5 px-3 py-2 rounded-xl font-bold text-[10px] uppercase tracking-wide transition whitespace-nowrap">
                ❓ <span>Mengapa</span>
            </button>
            <button type="button" @click="activeTab = 'specialist'" :class="activeTab === 'specialist' ? 'bg-green-500 text-white shadow' : 'text-slate-500 hover:bg-slate-100'" class="shrink-0 flex items-center gap-1.5 px-3 py-2 rounded-xl font-bold text-[10px] uppercase tracking-wide transition whitespace-nowrap">
                👩‍💼 <span>Specialist</span>
            </button>
            <button type="button" @click="activeTab = 'testimonials'" :class="activeTab === 'testimonials' ? 'bg-amber-400 text-white shadow' : 'text-slate-500 hover:bg-slate-100'" class="shrink-0 flex items-center gap-1.5 px-3 py-2 rounded-xl font-bold text-[10px] uppercase tracking-wide transition whitespace-nowrap">
                💬 <span>Ulasan</span>
            </button>
            <button type="button" @click="activeTab = 'stats'" :class="activeTab === 'stats' ? 'bg-slate-800 text-white shadow' : 'text-slate-500 hover:bg-slate-100'" class="shrink-0 flex items-center gap-1.5 px-3 py-2 rounded-xl font-bold text-[10px] uppercase tracking-wide transition whitespace-nowrap">
                📊 <span>Statistik</span>
            </button>
            <button type="button" @click="activeTab = 'seo'" :class="activeTab === 'seo' ? 'bg-green-800 text-white shadow' : 'text-slate-500 hover:bg-slate-100'" class="shrink-0 flex items-center gap-1.5 px-3 py-2 rounded-xl font-bold text-[10px] uppercase tracking-wide transition whitespace-nowrap">
                🔍 <span>SEO</span>
            </button>
            <button type="button" @click="activeTab = 'detailpage'" :class="activeTab === 'detailpage' ? 'bg-slate-900 text-white shadow' : 'text-slate-500 hover:bg-slate-100'" class="shrink-0 flex items-center gap-1.5 px-3 py-2 rounded-xl font-bold text-[10px] uppercase tracking-wide transition whitespace-nowrap">
                📄 <span>Halaman Detail</span>
            </button>
        </div>

        <form action="{{ route('admin.cms.save', 'cms_tour') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            {{-- Daftar yang boleh dikosongkan total. Kalau semua itemnya dihapus,
                 browser tidak mengirim key-nya sama sekali; tanpa ini data lama akan bertahan. --}}
            <input type="hidden" name="_clear_if_empty" value="homepage_slides,testimonials,featured_package_ids,detail_usp">

            {{-- Blok khusus halaman detail tanpa form (/tour/detail).
                 Isinya global: sekali ditulis, berlaku untuk semua paket --
                 admin tidak perlu mengetik ulang kalimat pembeda yang sama di
                 delapan paket, lalu lupa memperbaruinya di tujuh di antaranya. --}}
            <div x-show="activeTab === 'detailpage'" x-transition
                 x-data="{ usp: {{ \Illuminate\Support\Js::from(array_values($settings['detail_usp'] ?? [])) }} }"
                 class="bg-white rounded-[2.5rem] p-8 border border-slate-100 shadow-sm space-y-8">

                <div>
                    <h4 class="text-sm font-black text-slate-900 uppercase tracking-widest flex items-center gap-2 mb-1">
                        <span class="w-2 h-2 rounded-full bg-slate-900"></span> Keterangan Keaslian Video
                    </h4>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-3">Muncul di bawah galeri video</p>
                    <input type="text" name="video_credit_note"
                           value="{{ $settings['video_credit_note'] ?? '' }}"
                           placeholder="Semua video di atas rekaman tim kami sendiri, bukan stok."
                           class="w-full px-4 py-3 bg-slate-50 rounded-2xl border-none font-bold text-xs text-slate-900">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h4 class="text-sm font-black text-slate-900 uppercase tracking-widest flex items-center gap-2 mb-1">
                                <span class="w-2 h-2 rounded-full bg-slate-900"></span> Kenapa Kami Berbeda
                            </h4>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">3-4 poin pembeda</p>
                        </div>
                        <button type="button" @click="usp.push({ title: '', text: '' })"
                                class="px-4 py-2 bg-slate-900 text-white rounded-xl font-black text-[9px] uppercase tracking-widest shadow-lg">
                            + Tambah Poin
                        </button>
                    </div>

                    <div class="space-y-3">
                        <template x-for="(item, idx) in usp" :key="'usp' + idx">
                            <div class="p-5 bg-slate-50 rounded-3xl space-y-3 relative group">
                                <button type="button" @click="usp.splice(idx, 1)"
                                        class="absolute top-4 right-4 w-6 h-6 rounded-full bg-white text-rose-500 shadow-sm flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                    <i class="fas fa-times text-[10px]"></i>
                                </button>
                                <input type="text" x-model="item.title" placeholder="Judul poin"
                                       :name="'detail_usp[' + idx + '][title]'"
                                       class="w-full px-3 py-2 bg-white rounded-xl border-none font-black text-[11px] text-slate-900">
                                <textarea x-model="item.text" rows="2" placeholder="Penjelasan singkat"
                                          :name="'detail_usp[' + idx + '][text]'"
                                          class="w-full px-3 py-2 bg-white rounded-xl border-none font-bold text-[10px] text-slate-600"></textarea>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-[2.5rem] p-8 border border-slate-100 shadow-sm overflow-hidden">
                <!-- Hero Tab -->
                <div x-show="activeTab === 'hero'" x-transition class="space-y-6">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-sm font-black text-toba-green uppercase tracking-widest flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-toba-green"></span> Tampilan Hero Utama
                        </h4>
                        <label class="flex items-center cursor-pointer gap-2">
                            <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Tampilkan</span>
                            <div class="relative inline-block w-8 h-4">
                                <input type="hidden" name="show_hero" value="0">
                                <input type="checkbox" name="show_hero" value="1" {{ ($settings['show_hero'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-full h-full bg-slate-200 rounded-full peer peer-checked:bg-toba-green transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-3 after:w-3 after:transition peer-checked:after:translate-x-4"></div>
                            </div>
                        </label>
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Headline Utama</label>
                            <textarea name="hero_title" x-model="heroTitle" rows="3" class="w-full px-5 py-3.5 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-toba-green font-black text-sm text-slate-900"></textarea>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Sub-headline</label>
                            <textarea name="hero_subtitle" x-model="heroSubtitle" rows="4" class="w-full px-5 py-3.5 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-toba-green font-bold text-slate-600 text-xs leading-relaxed"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Tombol Label</label>
                                <input type="text" name="hero_cta_text" x-model="ctaText" class="w-full px-5 py-3.5 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-toba-green font-bold text-slate-900 text-xs">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Target Link</label>
                                <input type="text" name="hero_cta_link" value="{{ $settings['hero_cta_link'] ?? '/packages' }}" class="w-full px-5 py-3.5 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-toba-green font-bold text-slate-900 text-xs">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Background Hero Utama</label>
                            <div class="relative group/hero overflow-hidden rounded-3xl bg-slate-900 aspect-video lg:aspect-[21/9] border-4 border-white shadow-2xl shadow-slate-200">
                                <img :src="heroImage" class="w-full h-full object-cover transition-transform duration-700 group-hover/hero:scale-110">
                                <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover/hero:opacity-100 transition flex flex-col items-center justify-center gap-3">
                                    <button type="button" @click="openMedia('hero')" class="px-6 py-3 bg-white text-slate-900 rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-2xl hover:scale-105 transition-transform flex items-center gap-2">
                                        <i class="fas fa-images"></i> Pilih dari Media Library
                                    </button>
                                </div>
                                <input type="hidden" name="hero_image_url" x-ref="heroUrl" :value="heroImage.replace('/storage/', '').replace(/^\//, '')">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Featured Packages Tab -->
                <div x-show="activeTab === 'featured'" x-transition class="space-y-5">
                    <div>
                        <h4 class="text-sm font-black text-amber-600 uppercase tracking-widest flex items-center gap-2 mb-1">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span> Paket Unggulan Homepage
                        </h4>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Pilih maks. 3 paket yang tampil di beranda Tour. Jika kosong, sistem pakai paket bertanda ⭐ Featured.</p>
                    </div>
                    <div class="space-y-3 max-h-[450px] overflow-y-auto pr-1 no-scrollbar">
                        @forelse($allTourPackages as $ap)
                        @php
                            $apImg = $ap->packageImages->first()?->image_url
                                   ?? $ap->resolveImageUrl($ap->images[0] ?? null);
                        @endphp
                        <label class="flex items-center gap-4 p-4 rounded-2xl cursor-pointer transition bg-slate-50 hover:bg-amber-50 hover:border hover:border-amber-200">
                            <input type="checkbox" id="pkg_{{ $ap->id }}" name="featured_package_ids[]" value="{{ $ap->id }}"
                                   {{ in_array($ap->id, (array)$pinnedIds) ? 'checked' : '' }}
                                   class="w-4 h-4 accent-amber-500 rounded shrink-0">
                            <img src="{{ imageUrl($apImg) }}" alt="{{ $ap->name }}" class="w-12 h-12 rounded-xl object-cover shrink-0" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                            <div class="w-12 h-12 rounded-xl bg-slate-200 items-center justify-center shrink-0 hidden">
                                <i class="fas fa-image text-slate-400 text-xs"></i>
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="font-black text-slate-900 text-xs truncate">{{ $ap->name }}</p>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">{{ $ap->locationTag ?? 'Sumatera Utara' }} · {{ \App\Helpers\CurrencyHelper::formatIn($ap->price, 'MYR') }}</p>
                            </div>
                            @if($ap->isFeatured)
                                <span class="text-amber-400 text-xs">⭐</span>
                            @endif
                        </label>
                        @empty
                        <div class="text-center py-8 text-slate-400">
                            <i class="fas fa-box-open text-3xl mb-2"></i>
                            <p class="text-[10px] font-bold uppercase tracking-widest">Belum ada paket aktif</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Why Us Tab -->
                <div x-show="activeTab === 'about'" x-transition class="space-y-6">
                    <h4 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-slate-900"></span> Kelebihan Sujai Toba Sumatera
                    </h4>
                    <div class="space-y-6 max-h-[500px] overflow-y-auto pr-2 no-scrollbar">
                        @for($i = 1; $i <= 3; $i++)
                        <div class="p-5 bg-slate-50 rounded-3xl space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Kelebihan #{{ $i }}</span>
                                <i class="fas {{ $i == 1 ? 'fa-gem' : ($i == 2 ? 'fa-user-tie' : 'fa-hand-holding-heart') }} text-slate-300"></i>
                            </div>
                            <input type="text" name="about_title_{{ $i }}" value="{{ $settings['about_title_'.$i] ?? 'Layanan Exclusive' }}" class="w-full px-4 py-2.5 bg-white border-none rounded-xl font-black text-xs text-slate-900" placeholder="Judul...">
                            <textarea name="about_desc_{{ $i }}" rows="2" class="w-full px-4 py-2.5 bg-white border-none rounded-xl font-bold text-[10px] text-slate-500 leading-relaxed" placeholder="Deskripsi singkat...">{{ $settings['about_desc_'.$i] ?? 'Kami mengedepankan kenyamanan tamu dengan standar hotel dan armada terbaik.' }}</textarea>
                        </div>
                        @endfor

                        {{-- Gallery Images for "Why Us" section --}}
                        <div class="pt-2 border-t border-slate-100">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-4">🖼️ Foto Galeri (Tampil di sisi kiri "Mengapa Kami")</p>
                            <div class="space-y-4">
                                @foreach([['why_image_1', 'Foto Utama (Besar, Kiri)'], ['why_image_2', 'Foto Kanan Atas'], ['why_image_3', 'Foto Kanan Bawah']] as [$fieldName, $label])
                                <div class="p-4 bg-slate-50 rounded-2xl space-y-2">
                                    <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest">{{ $label }}</label>
                                    <div class="relative group cursor-pointer aspect-video rounded-xl bg-white border-2 border-dashed border-slate-200 overflow-hidden flex items-center justify-center hover:bg-slate-50 transition">
                                        <div class="absolute inset-0 z-10" @click="openMedia('{{ $fieldName }}')"></div>
                                        @if(!empty($settings[$fieldName.'_url']))
                                            <img src="{{ imageUrl($settings[$fieldName.'_url']) }}" class="w-full h-full object-cover" id="preview_{{ $fieldName }}">
                                        @else
                                            <div class="text-center" id="empty_{{ $fieldName }}">
                                                <i class="fas fa-cloud-arrow-up text-slate-300 text-xl mb-1"></i>
                                                <p class="text-[8px] font-bold text-slate-300 uppercase tracking-widest">Pilih dari Galeri</p>
                                            </div>
                                            <img src="" class="w-full h-full object-cover hidden" id="preview_{{ $fieldName }}">
                                        @endif
                                        <input type="hidden" name="{{ $fieldName }}_url" value="{{ $settings[$fieldName.'_url'] ?? '' }}" id="input_{{ $fieldName }}">
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Tab -->
                <div x-show="activeTab === 'stats'" x-transition class="space-y-6">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-sm font-black text-slate-900 uppercase tracking-widest flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-slate-900"></span> Statistik Sujai Toba Sumatera
                        </h4>
                        <label class="flex items-center cursor-pointer gap-2">
                            <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Tampilkan</span>
                            <div class="relative inline-block w-8 h-4">
                                <input type="hidden" name="show_stats" value="0">
                                <input type="checkbox" name="show_stats" value="1" {{ ($settings['show_stats'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-full h-full bg-slate-200 rounded-full peer peer-checked:bg-slate-900 transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-3 after:w-3 after:transition peer-checked:after:translate-x-4"></div>
                            </div>
                        </label>
                    </div>
                    <div class="grid grid-cols-1 gap-4">
                        @foreach(range(0, 3) as $idx)
                        <div class="flex gap-4 items-end bg-slate-50 p-4 rounded-2xl">
                            <div class="flex-1 space-y-2">
                                <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Angka</label>
                                <input type="text" name="stat_value_{{ $idx }}" x-model="stat{{ $idx }}" class="w-full px-4 py-2 bg-white border-none rounded-xl font-black text-xs">
                            </div>
                            <div class="flex-[2] space-y-2">
                                <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Keterangan</label>
                                <input type="text" name="stat_label_{{ $idx }}" x-model="label{{ $idx }}" class="w-full px-4 py-2 bg-white border-none rounded-xl font-bold text-xs">
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Specialist Tab -->
                <div x-show="activeTab === 'specialist'" x-transition class="space-y-6">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-sm font-black text-green-500 uppercase tracking-widest flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-green-500"></span> Travel Specialist
                        </h4>
                        <label class="flex items-center cursor-pointer gap-2">
                            <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Tampilkan</span>
                            <div class="relative inline-block w-8 h-4">
                                <input type="hidden" name="show_specialist" value="0">
                                <input type="checkbox" name="show_specialist" value="1" {{ ($settings['show_specialist'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-full h-full bg-slate-200 rounded-full peer peer-checked:bg-green-500 transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-3 after:w-3 after:transition peer-checked:after:translate-x-4"></div>
                            </div>
                        </label>
                    </div>
                    <div class="space-y-4">
                        <div class="flex items-center gap-6 p-6 bg-slate-50 rounded-[2rem] border border-slate-100">
                            <div class="relative group cursor-pointer w-24 h-24 rounded-full overflow-hidden border-4 border-white shadow-xl flex-shrink-0">
                                <img :src="fixPath(specialist_image)" class="w-full h-full object-cover">
                                <div @click="openMedia('specialist')" class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <i class="fas fa-camera text-white text-lg"></i>
                                </div>
                                <input type="hidden" name="specialist_image_url" :value="specialist_image">
                            </div>
                            <div class="flex-1 space-y-3">
                                <div class="space-y-1">
                                    <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Nama Lengkap</label>
                                    <input type="text" name="specialist_name" x-model="specialist_name" class="w-full px-4 py-2 bg-white border-none rounded-xl font-black text-xs">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Nomor WhatsApp</label>
                                    <input type="text" name="specialist_wa" x-model="specialist_wa" placeholder="Contoh: 081234567890" class="w-full px-4 py-2 bg-white border-none rounded-xl font-black text-xs">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Jabatan / Title</label>
                                    <input type="text" name="specialist_title" x-model="specialist_title" class="w-full px-4 py-2 bg-white border-none rounded-xl font-bold text-[10px] text-slate-500">
                                </div>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Pesan Sapaan (Deskripsi)</label>
                            <textarea name="specialist_desc" x-model="specialist_desc" rows="4" class="w-full px-5 py-3.5 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-green-500 font-medium text-slate-600 text-xs leading-relaxed"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Testimonials Tab -->
                <div x-show="activeTab === 'testimonials'" x-transition class="space-y-6">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-sm font-black text-amber-400 uppercase tracking-widest flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-400"></span> Ulasan Pengunjung
                        </h4>
                        <label class="flex items-center cursor-pointer gap-2">
                            <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Tampilkan</span>
                            <div class="relative inline-block w-8 h-4">
                                <input type="hidden" name="show_testimonials" value="0">
                                <input type="checkbox" name="show_testimonials" value="1" {{ ($settings['show_testimonials'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-full h-full bg-slate-200 rounded-full peer peer-checked:bg-amber-400 transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-3 after:w-3 after:transition peer-checked:after:translate-x-4"></div>
                            </div>
                        </label>
                    </div>
                    {{-- Judul bagian ini dulu tertanam di kode. Sekarang diatur
                         di sini supaya bisa diubah tanpa menyentuh Blade. --}}
                    <div class="p-5 bg-slate-50 rounded-3xl space-y-4">
                        <div class="space-y-1">
                            <label class="text-[7px] font-black text-slate-400 uppercase tracking-widest">Label Kecil (di atas judul)</label>
                            <input type="text" name="testimonials_eyebrow" value="{{ $settings['testimonials_eyebrow'] ?? '' }}"
                                   placeholder="Testimoni Wisatawan"
                                   class="w-full px-3 py-1.5 bg-white border-none rounded-lg font-bold text-[10px]">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[7px] font-black text-slate-400 uppercase tracking-widest">Judul Bagian</label>
                            <input type="text" name="testimonials_title" value="{{ $settings['testimonials_title'] ?? '' }}"
                                   placeholder="Apa Kata Mereka Tentang Sujai Toba Sumatera?"
                                   class="w-full px-3 py-1.5 bg-white border-none rounded-lg font-black text-[11px]">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[7px] font-black text-slate-400 uppercase tracking-widest">Deskripsi (opsional)</label>
                            <textarea name="testimonials_subtitle" rows="2"
                                      placeholder="Kosongkan kalau tidak perlu. Hindari klaim angka yang belum bisa dibuktikan."
                                      class="w-full px-3 py-2 bg-white border-none rounded-xl font-medium text-[9px] leading-relaxed">{{ $settings['testimonials_subtitle'] ?? '' }}</textarea>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="button" @click="addTestimonial()" class="px-4 py-2 bg-amber-500 text-white rounded-xl font-black text-[9px] uppercase tracking-widest shadow-lg shadow-amber-200">
                            + Tambah Ulasan
                        </button>
                    </div>
                    <div class="space-y-4 max-h-[500px] overflow-y-auto pr-2 no-scrollbar">
                        <template x-for="(t, idx) in testimonials" :key="idx">
                            <div class="p-5 bg-slate-50 rounded-3xl space-y-4 relative group">
                                <button type="button" @click="removeTestimonial(idx)" class="absolute top-4 right-4 w-6 h-6 rounded-full bg-white text-rose-500 shadow-sm flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                    <i class="fas fa-times text-[10px]"></i>
                                </button>
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-white border border-slate-100 overflow-hidden shrink-0 relative group/avatar cursor-pointer">
                                        <img :src="fixPath(t.image)" class="w-full h-full object-cover">
                                        <div @click="openMedia('testimonial', idx)" class="absolute inset-0 bg-black/40 opacity-0 group-hover/avatar:opacity-100 transition-opacity flex items-center justify-center">
                                            <i class="fas fa-camera text-white text-[10px]"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1 grid grid-cols-2 gap-3">
                                        <div class="space-y-1">
                                            <label class="text-[7px] font-black text-slate-400 uppercase tracking-widest">Nama</label>
                                            <input type="text" :name="'testimonials['+idx+'][name]'" x-model="t.name" class="w-full px-3 py-1.5 bg-white border-none rounded-lg font-black text-[10px]">
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-[7px] font-black text-slate-400 uppercase tracking-widest">Lokasi</label>
                                            <input type="text" :name="'testimonials['+idx+'][location]'" x-model="t.location" class="w-full px-3 py-1.5 bg-white border-none rounded-lg font-bold text-[9px]">
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[7px] font-black text-slate-400 uppercase tracking-widest">Ulasan</label>
                                    <textarea :name="'testimonials['+idx+'][text]'" x-model="t.text" rows="2" class="w-full px-3 py-2 bg-white border-none rounded-xl font-medium text-[9px] leading-relaxed"></textarea>
                                </div>
                                <input type="hidden" :name="'testimonials['+idx+'][image]'" :value="t.image">
                            </div>
                        </template>
                    </div>
                </div>

                <!-- SEO Tab -->
                <div x-show="activeTab === 'seo'" x-transition class="space-y-6">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-2xl bg-green-100 text-green-800 flex items-center justify-center">
                            <i class="fas fa-search"></i>
                        </div>
                        <h4 class="text-sm font-black text-slate-900 uppercase tracking-widest">Optimasi SEO Halaman</h4>
                    </div>
                    
                    <div class="space-y-6">
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">SEO Meta Title</label>
                            <input type="text" name="meta_title" value="{{ $settings['meta_title'] ?? '' }}" placeholder="Sujai Toba Sumatera | Paket Wisata Terbaik" class="w-full px-5 py-3.5 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-green-800 font-bold text-slate-900 text-xs shadow-inner">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">SEO Meta Description</label>
                            <textarea name="meta_description" rows="4" class="w-full px-5 py-3.5 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-green-800 font-bold text-slate-600 text-xs leading-relaxed shadow-inner" placeholder="Jelajahi keindahan Danau Toba dengan paket wisata premium kami...">{{ $settings['meta_description'] ?? '' }}</textarea>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">SEO Meta Keywords</label>
                            <input type="text" name="meta_keywords" value="{{ $settings['meta_keywords'] ?? '' }}" placeholder="wisata toba, tour samosir, travel medan" class="w-full px-5 py-3.5 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-green-800 font-bold text-slate-900 text-xs shadow-inner">
                        </div>
                    </div>
                </div>

                <!-- Slider Tab (Now as the Editor) -->
                <div x-show="activeTab === 'slider'" x-transition class="space-y-6">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-sm font-black text-green-800 uppercase tracking-widest flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-green-800"></span> 📸 Dynamic Hero Slider
                        </h4>
                        <label class="flex items-center cursor-pointer gap-2">
                            <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Tampilkan</span>
                            <div class="relative inline-block w-8 h-4">
                                <input type="hidden" name="show_slider" value="0">
                                <input type="checkbox" name="show_slider" value="1" {{ ($settings['show_slider'] ?? true) ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-full h-full bg-slate-200 rounded-full peer peer-checked:bg-green-800 transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-3 after:w-3 after:transition peer-checked:after:translate-x-4"></div>
                            </div>
                        </label>
                    </div>

                    <!-- Panduan Ukuran Hero Slider -->
                    <div class="p-4 rounded-2xl bg-green-50 border border-green-200 text-xs text-slate-700 space-y-2 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 font-black text-slate-900 text-xs uppercase tracking-wider">
                                <i class="fas fa-ruler-combined text-toba-green"></i>
                                <span>Standar Ukuran Hero Slider</span>
                            </div>
                            <button type="button" @click="$dispatch('open-image-guide')"
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-green-700 hover:bg-green-800 text-white font-bold text-[10px] transition shadow-sm">
                                <i class="fas fa-table-list text-[9px]"></i>
                                <span>Lihat Semua Ukuran</span>
                            </button>
                        </div>
                        <p class="text-xs leading-relaxed text-slate-600">
                            • <strong>Desktop (16:9):</strong> <span class="font-bold text-slate-900">1920 &times; 1080 px</span> (minimal 1600 &times; 900 px). Safe zone 15% dari tepi.<br>
                            • <strong>Mobile (4:5 / 1:1):</strong> <span class="font-bold text-slate-900">1080 &times; 1350 px</span> atau 800 &times; 800 px untuk layar smartphone.<br>
                            • <strong>Catatan:</strong> Lanskap luas Danau Toba. Area tengah bawah sedikit digelapkan agar teks judul putih terbaca jelas.
                        </p>
                    </div>

                    <div class="flex justify-end">
                        <button type="button" @click="addSlide()" class="px-4 py-2 bg-green-800 text-white rounded-xl font-black text-[9px] uppercase tracking-widest shadow-lg shadow-green-300 hover:bg-green-900 transition flex items-center gap-2">
                            <i class="fas fa-plus"></i> Tambah Slide
                        </button>
                    </div>

                    <div class="space-y-6 max-h-[60vh] overflow-y-auto pr-2 no-scrollbar">
                        <template x-for="(slide, index) in slides" :key="index">
                            <div class="bg-slate-50 rounded-3xl p-5 border border-slate-100 relative group">
                                <button type="button" @click="removeSlide(index)" class="absolute top-4 right-4 w-7 h-7 rounded-full bg-white text-slate-300 hover:text-rose-500 shadow-sm flex items-center justify-center transition opacity-0 group-hover:opacity-100 z-10">
                                    <i class="fas fa-trash-can text-[10px]"></i>
                                </button>

                                <div class="space-y-5">
                                    <!-- Slide Header -->
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-slate-900 text-white flex items-center justify-center font-black text-[10px]" x-text="index + 1"></div>
                                        <select x-model="slide.type" class="flex-1 bg-white border-none rounded-xl font-black text-[10px] uppercase tracking-widest p-2 text-slate-900">
                                            <option value="manual">Manual / Custom</option>
                                            <option value="package">Paket Tour</option>
                                            <option value="blog">Blog Post</option>
                                            <option value="gallery">Galeri Foto</option>
                                        </select>
                                    </div>

                                    <!-- Package Picker -->
                                    <div x-show="slide.type === 'package'" class="space-y-2">
                                        <p class="text-[8px] font-black text-green-800 uppercase tracking-widest">Pilih Paket Tour</p>
                                        <div class="grid grid-cols-1 gap-2 max-h-[150px] overflow-y-auto no-scrollbar bg-white p-2 rounded-2xl border border-slate-100">
                                            @foreach($packages as $p)
                                            <button type="button" 
                                                    @click="importFromPackage({
                                                        id: {{ $p->id }},
                                                        name: '{{ addslashes($p->name) }}',
                                                        shortDescription: '{{ addslashes($p->shortDescription) }}',
                                                        slug: '{{ $p->slug }}',
                                                        locationTag: '{{ addslashes($p->locationTag) }}',
                                                        price: {{ $p->price ?: 0 }},
                                                        duration: '{{ addslashes($p->duration) }}',
                                                        image_path: '{{ imageUrl($p->packageImages->first()?->image_path ?? ($p->images[0] ?? '')) }}'
                                                    })"
                                                    class="flex items-center gap-2 p-2 rounded-xl hover:bg-green-100 transition text-left group/pkg">
                                                <div class="w-8 h-8 rounded-lg bg-slate-100 overflow-hidden shrink-0">
                                                    <img src="{{ imageUrl($p->packageImages->first()?->image_path ?? ($p->images[0] ?? '')) }}" class="w-full h-full object-cover">
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-[9px] font-black text-slate-900 truncate">{{ $p->name }}</p>
                                                    <p class="text-[7px] font-bold text-slate-400 uppercase tracking-widest">{{ \App\Helpers\CurrencyHelper::formatIn($p->price, 'MYR') }}</p>
                                                </div>
                                            </button>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Blog Picker -->
                                    <div x-show="slide.type === 'blog'" class="space-y-2">
                                        <p class="text-[8px] font-black text-orange-600 uppercase tracking-widest">Pilih Artikel Blog</p>
                                        <div class="grid grid-cols-1 gap-2 max-h-[150px] overflow-y-auto no-scrollbar bg-white p-2 rounded-2xl border border-slate-100">
                                            @foreach($blogs as $b)
                                            <button type="button" 
                                                    @click="importFromBlog({
                                                        id: {{ $b->id }},
                                                        title: '{{ addslashes($b->title) }}',
                                                        shortDescription: '{{ addslashes($b->shortDescription) }}',
                                                        slug: '{{ $b->slug }}',
                                                        thumbnail: '{{ imageUrl($b->thumbnail) }}'
                                                    })"
                                                    class="flex items-center gap-2 p-2 rounded-xl hover:bg-orange-50 transition text-left group/blog">
                                                <div class="w-8 h-8 rounded-lg bg-slate-100 overflow-hidden shrink-0">
                                                    <img src="{{ imageUrl($b->thumbnail) }}" class="w-full h-full object-cover">
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-[9px] font-black text-slate-900 truncate">{{ $b->title }}</p>
                                                    <p class="text-[7px] font-bold text-slate-400 uppercase tracking-widest">{{ $b->createdAt->format('d M Y') }}</p>
                                                </div>
                                            </button>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Gallery Picker -->
                                    <div x-show="slide.type === 'gallery'" class="space-y-2">
                                        <p class="text-[8px] font-black text-green-600 uppercase tracking-widest">Pilih Foto Galeri</p>
                                        <div class="grid grid-cols-2 gap-2 max-h-[150px] overflow-y-auto no-scrollbar bg-white p-2 rounded-2xl border border-slate-100">
                                            @foreach($gallery as $g)
                                            <button type="button" 
                                                    @click="importFromGallery({
                                                        id: {{ $g->id }},
                                                        title: '{{ addslashes($g->title) }}',
                                                        description: '{{ addslashes($g->description) }}',
                                                        image_path: '{{ imageUrl($g->image_path) }}'
                                                    })"
                                                    class="flex flex-col gap-2 p-1 rounded-xl hover:bg-green-50 transition text-left group/gallery">
                                                <div class="w-full aspect-video rounded-lg bg-slate-100 overflow-hidden">
                                                    <img src="{{ imageUrl($g->image_path) }}" class="w-full h-full object-cover">
                                                </div>
                                                <p class="text-[8px] font-black text-slate-900 truncate px-1">{{ $g->title ?? 'Untitled' }}</p>
                                            </button>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Image Preview -->
                                    <div class="w-full h-32 rounded-2xl bg-white border border-slate-200 overflow-hidden relative group/img">
                                        <img :src="fixPath(slide.image_url) || 'https://via.placeholder.com/800x400?text=Pilih+Gambar'" class="w-full h-full object-cover">
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover/img:opacity-100 transition-opacity flex flex-col items-center justify-center gap-2">
                                            <button type="button" @click="openMedia('slider')" class="px-3 py-1.5 bg-white text-slate-900 rounded-lg font-black text-[8px] uppercase tracking-widest shadow-xl hover:scale-105 transition-transform">
                                                <i class="fas fa-images mr-1"></i> Pilih dari Galeri
                                            </button>
                                            <p class="text-[6px] font-black text-white/60 uppercase tracking-[0.2em]">Atau Upload File di Bawah</p>
                                        </div>
                                    </div>

                                    <!-- Slide Details -->
                                    <div class="space-y-3">
                                        <div class="grid grid-cols-2 gap-3">
                                            <div class="space-y-1">
                                                <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Headline</label>
                                                <input type="text" x-model="slide.title" class="w-full px-3 py-2 bg-white rounded-xl border-none font-black text-[10px] text-slate-900">
                                            </div>
                                            <div class="space-y-1">
                                                <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Lokasi</label>
                                                <input type="text" x-model="slide.location" class="w-full px-3 py-2 bg-white rounded-xl border-none font-bold text-[10px] text-slate-900">
                                            </div>
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Deskripsi</label>
                                            <textarea x-model="slide.subtitle" rows="2" class="w-full px-3 py-2 bg-white rounded-xl border-none font-medium text-[9px] text-slate-500 leading-relaxed"></textarea>
                                        </div>
                                        <div class="grid grid-cols-2 gap-3">
                                            <div class="space-y-1">
                                                <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Background Image</label>
                                                <button type="button" @click="openMedia('slider', index)" class="w-full px-3 py-2 bg-white rounded-xl border-none flex items-center gap-2 hover:bg-slate-100 transition">
                                                    <i class="fas fa-images text-slate-300 text-[10px]"></i>
                                                    <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Pilih dari Galeri</span>
                                                </button>
                                            </div>
                                            <div class="space-y-1" x-show="slide.type === 'package' || slide.type === 'manual'">
                                                <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Harga</label>
                                                <input type="text" x-model="slide.price" class="w-full px-3 py-2 bg-white rounded-xl border-none font-bold text-[9px] text-slate-900">
                                            </div>
                                            <div class="space-y-1" x-show="slide.type === 'package' || slide.type === 'manual'">
                                                <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Durasi</label>
                                                <input type="text" x-model="slide.duration" class="w-full px-3 py-2 bg-white rounded-xl border-none font-bold text-[9px] text-slate-900">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- CTA Fields — Tombol & Link per slide -->
                                    <div class="grid grid-cols-2 gap-3">
                                        <div class="space-y-1">
                                            <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Teks Tombol</label>
                                            <input type="text" x-model="slide.cta_text" placeholder="Book Now!" class="w-full px-3 py-2 bg-white rounded-xl border-none font-bold text-[10px] text-slate-900">
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Target Link</label>
                                            <input type="text" x-model="slide.cta_link" placeholder="/tour/packages" class="w-full px-3 py-2 bg-white rounded-xl border-none font-bold text-[10px] text-slate-900">
                                        </div>
                                    </div>

                                    <!-- Hidden Inputs for Form Submission -->
                                    <input type="hidden" :name="'homepage_slides['+index+'][title]'" :value="slide.title">
                                    <input type="hidden" :name="'homepage_slides['+index+'][subtitle]'" :value="slide.subtitle">
                                    <input type="hidden" :name="'homepage_slides['+index+'][image_url]'" :value="slide.image_url">
                                    <input type="hidden" :name="'homepage_slides['+index+'][location]'" :value="slide.location">
                                    <input type="hidden" :name="'homepage_slides['+index+'][duration]'" :value="slide.duration">
                                    <input type="hidden" :name="'homepage_slides['+index+'][price]'" :value="slide.price">
                                    <input type="hidden" :name="'homepage_slides['+index+'][cta_text]'" :value="slide.cta_text">
                                    <input type="hidden" :name="'homepage_slides['+index+'][cta_link]'" :value="slide.cta_link">
                                    <input type="hidden" :name="'homepage_slides['+index+'][type]'" :value="slide.type">
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full py-5 bg-slate-900 text-white rounded-[2rem] font-black text-[10px] uppercase tracking-widest shadow-2xl hover:bg-toba-green transition flex items-center justify-center gap-3">
                <i class="fas fa-save"></i> Perbarui Halaman Tour
            </button>
        </form>
    </div>

    <!-- RIGHT: LIVE PREVIEW & SLIDER MANAGEMENT -->
    <div class="flex-1 bg-white rounded-[3.5rem] overflow-hidden relative shadow-[0_40px_100px_-20px_rgba(0,0,0,0.5)] border-8 border-slate-900/50 min-h-[600px] xl:h-[85vh] sticky top-8 overflow-y-auto no-scrollbar">
        
        <!-- Tab: SLIDER PREVIEW — 1:1 dengan tampilan homepage sesungguhnya -->
        <div x-show="activeTab === 'slider'" x-transition class="relative overflow-hidden bg-black" style="height:100%;">

            <!-- Slide Backgrounds -->
            <template x-for="(slide, index) in slides" :key="index">
                <div x-show="activeSlideIdx === index"
                     style="position:absolute;inset:0;transition:opacity 0.9s ease-in-out;"
                     :style="activeSlideIdx === index ? 'opacity:1;z-index:2;' : 'opacity:0;z-index:1;'">
                    <img :src="fixPath(slide.image_url) || '/images/sumut/toba_hero.webp'"
                         class="w-full h-full object-cover object-center"
                         style="display:block;">
                    <!-- Gradient overlay bawah — persis homepage -->
                    <div style="position:absolute;inset:0;background:linear-gradient(to bottom, rgba(0,0,0,0.04) 0%, rgba(0,0,0,0.10) 55%, rgba(0,0,0,0.55) 100%);z-index:2;"></div>
                </div>
            </template>

            <!-- BOOK NOW Button — tengah bawah, persis homepage -->
            <div style="position:absolute;bottom:72px;left:0;right:0;display:flex;justify-content:center;z-index:20;">
                <template x-for="(slide, index) in slides" :key="'btn-'+index">
                    <a x-show="activeSlideIdx === index"
                       :href="slide.cta_link || '#'"
                       style="display:inline-flex;align-items:center;gap:8px;background:#E67E22;color:#fff;font-weight:800;padding:12px 44px;border-radius:9999px;letter-spacing:0.12em;text-transform:uppercase;font-size:13px;border:2px solid rgba(255,255,255,0.35);box-shadow:0 6px 28px rgba(230,126,34,0.65);white-space:nowrap;text-decoration:none;animation:pulse-glow 2.5s infinite;">
                        <span x-text="slide.cta_text || 'Book Now!'"></span>
                    </a>
                </template>
            </div>

            <!-- Dot Indicators -->
            <div style="position:absolute;bottom:56px;left:50%;transform:translateX(-50%);z-index:25;display:flex;gap:7px;align-items:center;">
                <template x-for="(slide, index) in slides" :key="'dot-'+index">
                    <button type="button" @click="activeSlideIdx = index"
                            style="height:5px;border-radius:9999px;border:none;cursor:pointer;transition:all 0.35s;padding:0;"
                            :style="activeSlideIdx === index ? 'width:20px;background:white;' : 'width:5px;background:rgba(255,255,255,0.45);'">
                    </button>
                </template>
            </div>

            <!-- Arrow Prev/Next -->
            <button type="button" @click="activeSlideIdx = (activeSlideIdx - 1 + slides.length) % slides.length"
                    style="position:absolute;top:50%;left:12px;transform:translateY(-50%);z-index:25;width:36px;height:36px;border-radius:50%;background:rgba(0,0,0,0.38);color:white;display:flex;align-items:center;justify-content:center;border:1.5px solid rgba(255,255,255,0.25);cursor:pointer;backdrop-filter:blur(4px);margin-top:-25px;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <button type="button" @click="activeSlideIdx = (activeSlideIdx + 1) % slides.length"
                    style="position:absolute;top:50%;right:12px;transform:translateY(-50%);z-index:25;width:36px;height:36px;border-radius:50%;background:rgba(0,0,0,0.38);color:white;display:flex;align-items:center;justify-content:center;border:1.5px solid rgba(255,255,255,0.25);cursor:pointer;backdrop-filter:blur(4px);margin-top:-25px;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>

            <!-- Features Bar — overlay bawah, persis homepage -->
            <div style="position:absolute;bottom:0;left:0;right:0;z-index:20;background:rgba(15,15,15,0.88);backdrop-filter:blur(8px);display:flex;align-items:stretch;justify-content:center;">
                <div style="display:flex;align-items:center;gap:9px;padding:11px 16px;color:white;flex:1;justify-content:center;border-right:1px solid rgba(255,255,255,0.10);">
                    <div style="width:30px;height:30px;border-radius:50%;background:#E67E22;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="14" height="14" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </div>
                    <div><p style="font-weight:700;font-size:9px;text-transform:uppercase;letter-spacing:0.06em;">Booking Mudah</p><p style="font-size:8px;color:#999;">Tanpa Ribet</p></div>
                </div>
                <div style="display:flex;align-items:center;gap:9px;padding:11px 16px;color:white;flex:1;justify-content:center;border-right:1px solid rgba(255,255,255,0.10);">
                    <div style="width:30px;height:30px;border-radius:50%;background:#E67E22;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="14" height="14" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div><p style="font-weight:700;font-size:9px;text-transform:uppercase;letter-spacing:0.06em;">Proses Cepat</p><p style="font-size:8px;color:#999;">CS 24/7 Fast Respon</p></div>
                </div>
                <div style="display:flex;align-items:center;gap:9px;padding:11px 16px;color:white;flex:1;justify-content:center;border-right:1px solid rgba(255,255,255,0.10);">
                    <div style="width:30px;height:30px;border-radius:50%;background:#E67E22;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="14" height="14" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div><p style="font-weight:700;font-size:9px;text-transform:uppercase;letter-spacing:0.06em;">Banyak Pilihan</p><p style="font-size:8px;color:#999;">Paket Fleksibel</p></div>
                </div>
                <div style="display:flex;align-items:center;gap:9px;padding:11px 16px;color:white;flex:1;justify-content:center;">
                    <div style="width:30px;height:30px;border-radius:50%;background:#E67E22;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="14" height="14" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div><p style="font-weight:700;font-size:9px;text-transform:uppercase;letter-spacing:0.06em;">Harga Terbaik</p><p style="font-size:8px;color:#999;">Terjangkau & Premium</p></div>
                </div>
            </div>

        </div>

        <!-- Tab: LIVE PREVIEW (Original Content) -->
        <div x-show="activeTab !== 'slider'" x-transition>
            <!-- Live Hero Section -->
            <section class="relative h-[500px] flex items-center px-5 md:px-12 lg:px-20 overflow-hidden">
                <div class="absolute inset-0 bg-cover bg-center transition duration-700" :style="`background-image: url('${heroImage}')`"></div>
                <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/40 to-transparent"></div>
                
                <div class="relative z-10 max-w-2xl space-y-8">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-px bg-green-500"></span>
                        <span class="text-green-400 text-[10px] font-black uppercase tracking-[0.3em]">Sujai Toba Sumatera Tour</span>
                    </div>
                    <h1 class="text-4xl md:text-6xl font-black text-white leading-[0.9] tracking-tighter" x-text="heroTitle"></h1>
                    <p class="text-slate-200 text-sm font-medium leading-relaxed max-w-lg opacity-80" x-text="heroSubtitle"></p>
                    <div class="pt-4">
                        <button type="button" class="px-8 py-4 bg-green-500 text-white rounded-full font-black text-[10px] uppercase tracking-widest shadow-xl shadow-green-500/20" x-text="ctaText"></button>
                    </div>
                </div>

                <!-- Scroll Indicator -->
                <div class="absolute bottom-10 left-12 md:left-20 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full border border-white/20 flex items-center justify-center text-white text-xs animate-bounce">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                    <span class="text-white/40 text-[8px] font-black uppercase tracking-widest">Scroll Explorasi</span>
                </div>
            </section>

            <!-- Live Stats Section -->
            <section class="py-6 px-5 md:px-12 lg:px-20 grid grid-cols-2 md:grid-cols-4 gap-8">
                <template x-for="i in [0,1,2,3]">
                    <div class="space-y-1">
                        <div class="text-3xl font-black text-slate-900 tracking-tighter" x-text="$data['stat'+i]"></div>
                        <div class="text-[8px] font-black text-slate-400 uppercase tracking-widest" x-text="$data['label'+i]"></div>
                    </div>
                </template>
                <div class="space-y-1">
                    <div class="text-3xl font-black text-slate-900 tracking-tighter">4.9/5</div>
                    <div class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Rating</div>
                </div>
            </section>

            <!-- Dummy Gallery Section -->
            <section class="px-5 md:px-12 lg:px-20 pb-10">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-xl font-black text-slate-900 tracking-tight">Galeri Perjalanan</h3>
                    <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">View All</span>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div class="aspect-square rounded-3xl bg-slate-100"></div>
                    <div class="aspect-square rounded-3xl bg-slate-100"></div>
                    <div class="aspect-square rounded-3xl bg-slate-100"></div>
                </div>
            </section>
        </div>

        <!-- Overlay Status -->
        <div class="absolute top-6 right-6 z-50 flex items-center gap-3">
            <div class="flex items-center gap-1.5 px-3 py-1.5 bg-green-500 text-white text-[8px] font-black uppercase tracking-widest rounded-lg shadow-lg shadow-green-500/20">
                <span class="w-1 h-1 rounded-full bg-white animate-pulse"></span> Tour Sync
            </div>
            <div class="px-3 py-1.5 bg-slate-900 text-white text-[8px] font-black uppercase tracking-widest rounded-lg">Live Preview</div>
        </div>
    </div>
</div>

@endsection

<style>
    [x-cloak] { display: none !important; }
    textarea { resize: none; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
</style>

