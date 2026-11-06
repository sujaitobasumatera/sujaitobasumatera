<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewBookingNotification extends Notification
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
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $packageName = $this->booking->package ? $this->booking->package->name : 'Paket Wisata';

        return (new MailMessage)
            ->subject('Pesanan Baru: '.$this->booking->bookingCode)
            ->greeting('Halo Admin,')
            ->line('Ada pesanan baru masuk ke sistem Sujai Toba Sumatera.')
            ->line('Kode Booking: '.$this->booking->bookingCode)
            ->line('Pelanggan: '.$this->booking->customerName)
            ->line('Paket: '.$packageName)
            ->line('Tanggal: '.$this->booking->startDate)
            ->action('Lihat Detail Booking', url('/admin/bookings/'.$this->booking->id))
            ->line('Silakan segera tindak lanjuti pesanan ini.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'booking_code' => $this->booking->bookingCode,
            'customer_name' => $this->booking->customerName,
            'message' => 'Pesanan baru dari '.$this->booking->customerName,
        ];
    }
}
