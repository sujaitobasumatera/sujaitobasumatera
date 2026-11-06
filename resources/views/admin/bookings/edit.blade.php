@extends('admin.layout')

@section('title', 'Edit Booking')
@section('page-title', 'Edit Booking #' . $booking->bookingCode)

@section('content')
<div class="max-w-4xl">
    <div class="mb-8">
        <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center text-sm font-black text-toba-green uppercase tracking-widest hover:text-green-700 transition">
            <i class="fas fa-arrow-left mr-2"></i> Back to Bookings
        </a>
    </div>

    <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-8 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-black text-gray-900">Update Booking Status</h2>
                <p class="text-sm text-gray-500 font-bold mt-1 uppercase tracking-wider">Customer: {{ $booking->customerName }}</p>
            </div>
            <div class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest {{ 
                $booking->status === 'confirmed' ? 'bg-green-100 text-green-700' : 
                ($booking->status === 'cancelled' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') 
            }}">
                {{ $booking->status }}
            </div>
        </div>

        <form action="{{ route('admin.bookings.update', $booking) }}" method="POST" class="p-8">
            @csrf
            @method('PATCH')

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-6">
                <!-- Status -->
                <div class="space-y-4">
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest">Update Status</label>
                    <select name="status" class="w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-toba-green/10 focus:border-toba-green transition font-bold text-gray-900">
                        <option value="pending" {{ $booking->status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="confirmed" {{ $booking->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="cancelled" {{ $booking->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="completed" {{ $booking->status === 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>

                <!-- Total Price -->
                <div class="space-y-4">
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest">Total Price Adjustment</label>
                    <div class="relative">
                        {{-- Nominal pesanan dibekukan dalam mata uangnya sendiri, jadi simbolnya ikut booking. --}}
                        <span class="absolute left-6 top-1/2 -translate-y-1/2 text-gray-400 font-bold">{{ trim(\App\Helpers\CurrencyHelper::config($booking->currency)['symbol']) }}</span>
                        <input type="number" name="totalPrice" value="{{ $booking->totalPrice }}" step="0.01" class="w-full pl-14 pr-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-toba-green/10 focus:border-toba-green transition font-bold text-gray-900">
                    </div>
                </div>

                <!-- Total Cost -->
                <div class="space-y-4">
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest">Total Cost (HPP)</label>
                    <div class="relative">
                        <span class="absolute left-6 top-1/2 -translate-y-1/2 text-gray-400 font-bold">Rp</span>
                        <input type="number" name="total_cost" value="{{ $booking->total_cost ?? 0 }}" class="w-full pl-14 pr-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-toba-green/10 focus:border-toba-green transition font-bold text-gray-900">
                    </div>
                </div>
            </div>

            <div class="space-y-4 mb-6">
                <label class="block text-xs font-black text-gray-400 uppercase tracking-widest">Internal Admin Notes</label>
                <textarea name="notes" rows="4" placeholder="Add private notes about this booking..." class="w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-toba-green/10 focus:border-toba-green transition font-medium text-gray-700">{{ $booking->notes }}</textarea>
            </div>

            <!-- Customer Read-only Info -->
            <div class="bg-gray-50/50 rounded-[1.5rem] p-6 border border-gray-100 mb-6">
                <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Customer Details (Read-only)</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase">Name</p>
                        <p class="text-sm font-bold text-gray-900">{{ $booking->customerName }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase">Email</p>
                        <p class="text-sm font-bold text-gray-900">{{ $booking->customerEmail }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase">Phone</p>
                        <p class="text-sm font-bold text-gray-900">{{ $booking->customerPhone }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase">Booking Date</p>
                        <p class="text-sm font-bold text-gray-900">{{ $booking->startDate ? $booking->startDate->format('d M Y') : '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <button type="submit" class="flex-1 bg-toba-green text-white py-4 rounded-2xl font-black uppercase tracking-widest hover:bg-green-700 transition shadow-xl shadow-toba-green/20">
                    Update Booking
                </button>
                <a href="{{ route('admin.bookings.index') }}" class="px-8 py-4 bg-gray-100 text-gray-600 rounded-2xl font-black uppercase tracking-widest hover:bg-gray-200 transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
