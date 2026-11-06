@extends('admin.layout')

@section('title', 'Clients Management')
@section('page-title', 'Corporate Clients')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900">Manage Clients</h1>
            <p class="text-gray-600 mt-1 text-sm">Logos of companies that have worked with us</p>
        </div>
    </div>

    <div class="bg-white rounded-[2rem] border border-gray-100 p-8 shadow-sm">
        <form action="{{ route('admin.clients.store') }}" method="POST" class="mb-6 p-6 bg-gray-50 rounded-2xl border border-gray-100">
            @csrf
            <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest mb-4">Add New Client</h3>
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-2 uppercase">Client Name</label>
                    <input type="text" name="name" required class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-toba-green transition text-sm">
                </div>
                
                <x-image-input 
                    name="logo"
                    label="Logo (webp/SVG)"
                    :value="null"
                    :required="false"
                    category="general"
                    help="Pilih atau upload logo client"
                />
                
                <button type="submit" class="bg-toba-green text-white px-6 py-2.5 rounded-xl font-bold hover:shadow-lg hover:shadow-toba-green/30 transition text-sm w-fit">
                    Upload Client
                </button>
            </div>
        </form>

        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
            @forelse($clients as $client)
                <div class="group relative aspect-video bg-white rounded-2xl border border-gray-100 flex items-center justify-center p-6 hover:shadow-md transition">
                    <img src="{{ $client->logo }}" alt="{{ $client->name }}" class="max-w-full max-h-full object-contain grayscale group-hover:grayscale-0 transition duration-500">
                    <form action="{{ route('admin.clients.destroy', $client) }}" method="POST" class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-6 h-6 rounded-lg bg-red-50 text-red-500 flex items-center justify-center hover:bg-red-500 hover:text-white transition">
                            <i class="fas fa-trash text-[10px]"></i>
                        </button>
                    </form>
                </div>
            @empty
                <div class="col-span-full text-center py-6">
                    <p class="text-gray-400 font-bold">No clients added yet</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function clientForm() {
        return {
            logoUrl: '',
            openMediaPicker() {
                window.dispatchEvent(new CustomEvent('open-media-picker', { 
                    detail: { 
                        callback: (item) => {
                            let path = item.path;
                            if (path.startsWith('/storage/')) path = path.replace('/storage/', '');
                            if (path.startsWith('storage/')) path = path.replace('storage/', '');
                            this.logoUrl = '/storage/' + path;
                        } 
                    } 
                }));
            }
        }
    }
</script>
@endpush
