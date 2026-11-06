<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingService;
use App\Services\ReportService;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BookingController extends Controller
{
    use LogsActivity;

    public function __construct(
        private BookingService $bookingService
    ) {}

    public function index(Request $request)
    {
        $filters = [
            'status' => $request->get('status'),
            'type' => $request->get('type'),
            'category' => $request->get('category'),
            'search' => $request->get('search'),
            'date' => $request->get('date'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
            'month' => $request->get('month'),
            'year' => $request->get('year'),
            'per_page' => $request->get('per_page', 15),
        ];

        $bookings = $this->bookingService->getAll($filters);

        return view('admin.bookings.index', compact('bookings'));
    }

    public function export(Request $request)
    {
        $filters = $request->only(['status', 'type', 'search', 'date', 'month', 'year']);

        $bookings = $this->bookingService->getAll($filters, true);

        $filename = 'bookings-export-'.date('Y-m-d').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($bookings) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Booking Code', 'Type', 'Item', 'Customer', 'Email', 'Phone', 'Start Date', 'End Date', 'Total Price', 'Status', 'Created At']);

            foreach ($bookings as $booking) {
                fputcsv($file, [
                    $booking->bookingCode,
                    ucfirst($booking->type),
                    $booking->package->name ?? 'N/A',
                    $booking->customerName,
                    $booking->customerEmail,
                    $booking->customerPhone,
                    $booking->startDate ? $booking->startDate->format('Y-m-d') : '',
                    $booking->endDate ? $booking->endDate->format('Y-m-d') : '',
                    $booking->totalPrice,
                    ucfirst($booking->status),
                    $booking->createdAt ? $booking->createdAt->format('Y-m-d H:i') : '',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $reportService = app(ReportService::class);
        $pdf = $reportService->generateBookingReport($startDate, $endDate);

        return $pdf->download('Booking-Report-'.date('Y-m-d').'.pdf');
    }

    public function create()
    {
        $packages = \App\Models\Package::where('status', 'active')->orderBy('name')->get();

        return view('admin.bookings.create', compact('packages'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:package',
            'packageId' => 'nullable|exists:packages,id',
            'startDate' => 'required|date|after_or_equal:today',
            'endDate' => 'required|date|after_or_equal:startDate',
            'pax' => 'required|integer|min:1|max:99',
            'customerName' => 'required|string|max:255',
            'customerEmail' => 'nullable|email',
            'customerPhone' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $booking = $this->bookingService->create($validated);
        $this->logActivity('created', "Created booking: {$booking->bookingCode}", $booking);
        Cache::forget('admin_dashboard_stats');

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('success', 'Booking created successfully');
    }

    public function show(Booking $booking)
    {
        return view('admin.bookings.show', compact('booking'));
    }

    public function edit(Booking $booking)
    {
        return view('admin.bookings.edit', compact('booking'));
    }

    public function update(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled',
            'totalPrice' => 'required|numeric|min:0',
            'total_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $this->bookingService->update($booking, $validated);
        $this->logActivity('updated', "Updated booking: {$booking->bookingCode}", $booking);
        Cache::forget('admin_dashboard_stats');

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('success', 'Booking updated successfully');
    }

    public function destroy(Booking $booking)
    {
        $code = $booking->bookingCode;
        $this->bookingService->delete($booking);
        $this->logActivity('deleted', "Deleted booking: {$code}");
        Cache::forget('admin_dashboard_stats');

        return redirect()
            ->route('admin.bookings.index')
            ->with('success', 'Booking deleted successfully');
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled',
        ]);

        $this->bookingService->updateStatus($booking, $validated['status']);
        $this->logActivity('status_updated', "Updated status of booking {$booking->bookingCode} to {$validated['status']}", $booking);
        Cache::forget('admin_dashboard_stats');

        return back()->with('success', 'Booking status updated successfully');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return response()->json(['message' => 'No IDs provided'], 400);
        }

        $this->bookingService->bulkDelete($ids);
        $this->logActivity('bulk_deleted', 'Bulk deleted '.count($ids).' bookings');
        Cache::forget('admin_dashboard_stats');

        return response()->json(['message' => 'Bookings deleted successfully']);
    }
}
