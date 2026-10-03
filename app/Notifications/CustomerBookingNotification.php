<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Services\InvoiceService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomerBookingNotification extends Notification
{
    use Queueable;

    protected $booking;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $packageName = $this->booking->package ? $this->booking->package->name : 'Paket Wisata';

        // Generate Invoice for attachment
        $invoiceService = app(InvoiceService::class);
        $pdf = $invoiceService->generateInvoice($this->booking);

        return (new MailMessage)
            ->subject('Konfirmasi Pemesanan: '.$this->booking->bookingCode)
            ->greeting('Halo '.$this->booking->customerName.',')
            ->line('Terima kasih telah memilih Sujai Toba Sumatera untuk rencana perjalanan Anda.')
            ->line('Pesanan Anda untuk paket **'.$packageName.'** telah kami terima dan saat ini sedang dalam proses verifikasi.')
            ->line('Kode Booking: **'.$this->booking->bookingCode.'**')
            ->line('Kami telah melampirkan invoice resmi pada email ini sebagai referensi pembayaran Anda.')
            ->attachData($pdf->output(), "Invoice-{$this->booking->bookingCode}.pdf", [
                'mime' => 'application/pdf',
            ])
            ->action('Lihat Detail Pesanan', url('/tour/package/'.($this->booking->package->slug ?? '')))
            ->line('Jika Anda memiliki pertanyaan, jangan ragu untuk menghubungi kami melalui WhatsApp.')
            ->line('Salam hangat, Tim Sujai Toba Sumatera.');
    }
}
