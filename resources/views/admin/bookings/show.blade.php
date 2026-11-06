@extends('admin.layout')

@section('title', 'Booking Details')
@section('page-title', 'Booking #' . $booking->bookingCode)

@section('content')
<div class="max-w-5xl">
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center text-sm font-black text-toba-green uppercase tracking-widest hover:text-green-700 transition">
            <i class="fas fa-arrow-left mr-2"></i> Back to Bookings
        </a>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.bookings.edit', $booking) }}" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl font-bold hover:bg-gray-200 transition text-sm">
                <i class="fas fa-edit mr-2"></i> Edit Booking
            </a>
            <a href="{{ route('invoice.download', $booking->bookingCode) }}" target="_blank" class="px-6 py-3 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 hover:shadow-lg transition text-sm">
                <i class="fas fa-file-pdf mr-2"></i> Invoice
            </a>
            <form action="{{ route('admin.bookings.status', $booking) }}" method="POST">
                @csrf
                @method('PATCH')
                @if($booking->status !== 'confirmed')
                    <input type="hidden" name="status" value="confirmed">
                    <button type="submit" class="px-6 py-3 bg-toba-green text-white rounded-xl font-bold hover:shadow-lg transition text-sm">
                        Confirm Booking
                    </button>
                @endif
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-8">
            <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden p-8">
                <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6">Booking Information</h3>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Service Type</p>
                        <div class="flex items-center">
                            <span class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center mr-3 text-toba-green">
                                <i class="fas fa-box"></i>
                            </span>
                            <p class="font-black text-gray-900 uppercase text-sm">{{ $booking->type }}</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Total Price</p>
                        <p class="text-xl font-black text-toba-green">{{ \App\Helpers\CurrencyHelper::formatIn($booking->totalPrice, $booking->currency) }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Start Date</p>
                        <p class="font-bold text-gray-900">{{ $booking->startDate ? $booking->startDate->format('l, d F Y') : '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">End Date</p>
                        <p class="font-bold text-gray-900">{{ $booking->endDate ? $booking->endDate->format('l, d F Y') : '-' }}</p>
                    </div>
                </div>

                @if($booking->notes)
                    <div class="mt-8 pt-8 border-t border-gray-50">
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-2">Admin Notes</p>
                        <div class="p-4 bg-gray-50 rounded-xl text-sm text-gray-600 italic">
                            "{{ $booking->notes }}"
                        </div>
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden p-8">
                <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6">Customer Information</h3>
                <div class="space-y-4">
                    <div class="flex items-center p-4 bg-gray-50 rounded-2xl border border-gray-100">
                        <div class="w-10 h-10 rounded-full bg-toba-green/10 flex items-center justify-center text-toba-green mr-4">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-400 font-bold uppercase">Full Name</p>
                            <p class="font-bold text-gray-900">{{ $booking->customerName }}</p>
                        </div>
                    </div>
                    <div class="flex items-center p-4 bg-gray-50 rounded-2xl border border-gray-100">
                        <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 mr-4">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-400 font-bold uppercase">Email Address</p>
                            <p class="font-bold text-gray-900">{{ $booking->customerEmail }}</p>
                        </div>
                    </div>
                    <div class="flex items-center p-4 bg-gray-50 rounded-2xl border border-gray-100">
                        <div class="w-10 h-10 rounded-full bg-green-50 flex items-center justify-center text-green-600 mr-4">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-400 font-bold uppercase">WhatsApp / Phone</p>
                            <p class="font-bold text-gray-900">{{ $booking->customerPhone }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="space-y-8">
            <div class="bg-white rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden p-8 text-center">
                <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-4">Current Status</p>
                <div class="inline-flex px-6 py-2 rounded-full text-xs font-black uppercase tracking-widest mb-6 {{ 
                    $booking->status === 'confirmed' ? 'bg-green-100 text-green-700 border border-green-200' : 
                    ($booking->status === 'cancelled' ? 'bg-red-100 text-red-700 border border-red-200' : 'bg-amber-100 text-amber-700 border border-amber-200') 
                }}">
                    {{ $booking->status }}
                </div>
                
                <div class="space-y-3">
                    <p class="text-[10px] text-gray-400 font-bold">Actions</p>
                    <div class="grid grid-cols-1 gap-2">
                        @if($booking->status !== 'completed')
                            <form action="{{ route('admin.bookings.status', $booking) }}" method="POST">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="completed">
                                <button type="submit" class="w-full py-3 rounded-xl bg-blue-600 text-white font-bold text-xs uppercase tracking-widest hover:bg-blue-700 transition">Mark as Completed</button>
                            </form>
                        @endif
                        @if($booking->status !== 'cancelled')
                            <form action="{{ route('admin.bookings.status', $booking) }}" method="POST">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="cancelled">
                                <button type="submit" class="w-full py-3 rounded-xl bg-gray-100 text-red-600 font-bold text-xs uppercase tracking-widest hover:bg-red-50 transition border border-red-100">Cancel Booking</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-toba-green to-green-700 rounded-[2rem] p-8 text-white shadow-xl shadow-toba-green/20">
                <i class="fas fa-info-circle text-2xl mb-4 opacity-50"></i>
                <h4 class="font-black text-lg mb-2 leading-tight">Need help with this booking?</h4>
                <p class="text-white/70 text-xs mb-6 font-medium">Contact technical support if you encounter issues with payment processing or database synchronization.</p>
                <button class="w-full py-3 bg-white/20 hover:bg-white/30 rounded-xl font-bold text-xs uppercase tracking-widest transition backdrop-blur-md">Contact IT Support</button>
            </div>
        </div>
    </div>
</div>
@endsection
