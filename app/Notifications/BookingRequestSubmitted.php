<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Notifications\Notification;

class BookingRequestSubmitted extends Notification
{
    public function __construct(public readonly Booking $booking) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
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
            'reference' => $this->booking->reference,
            'message' => 'Permohonan tempahan baharu diterima.',
        ];
    }
}
