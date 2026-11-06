@props(['items' => [], 'dark' => false])

@php
    // Beranda selalu jadi mata rantai pertama; pemanggil hanya menyebut sisanya.
    // Item terakhir adalah halaman yang sedang dibuka, jadi ia tidak ditautkan —
    // tautan yang mengarah ke halaman itu sendiri hanya menambah satu tab stop
    // tanpa memberi apa pun kepada pembaca maupun mesin pencari.
    // route('index'), bukan route('tour.index'): keduanya memanggil aksi yang
    // sama, tapi beranda yang dipakai navbar dan footer adalah '/'.
    $crumbs = array_merge(
        [['label' => __('Beranda'), 'url' => route('index')]],
        array_values(array_filter($items))
    );
    $last = count($crumbs) - 1;

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [],
    ];
    foreach ($crumbs as $i => $crumb) {
        $entry = [
            '@type' => 'ListItem',
            'position' => $i + 1,
            'name' => $crumb['label'],
        ];
        // Item terakhir tanpa 'item': schema.org memang menyatakan halaman
        // saat ini tidak perlu menautkan dirinya sendiri.
        if (! empty($crumb['url']) && $i !== $last) {
            $entry['item'] = $crumb['url'];
        }
        $schema['itemListElement'][] = $entry;
    }
@endphp

<nav aria-label="{{ __('Breadcrumb') }}" {{ $attributes->merge(['class' => 'w-full']) }}>
    <ol class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-medium">
        @foreach($crumbs as $i => $crumb)
            <li class="flex items-center gap-x-2">
                @if($i === $last || empty($crumb['url']))
                    <span aria-current="page" class="{{ $dark ? 'text-white' : 'text-slate-900' }} font-semibold">{{ $crumb['label'] }}</span>
                @else
                    <a href="{{ $crumb['url'] }}"
                       class="{{ $dark ? 'text-white/70 hover:text-white' : 'text-slate-500 hover:text-toba-green' }} transition rounded-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-toba-green">{{ $crumb['label'] }}</a>
                @endif

                @if($i !== $last)
                    <span aria-hidden="true" class="{{ $dark ? 'text-white/40' : 'text-slate-300' }}">/</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>

<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
