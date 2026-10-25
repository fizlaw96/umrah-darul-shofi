<?php

namespace App\Notifications;

use App\Models\Package;
use Illuminate\Notifications\Notification;

class PackageArchived extends Notification
{
    public function __construct(public readonly Package $package) {}

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
            'package_id' => $this->package->id,
            'package_title' => $this->package->title,
            'message' => "Pakej {$this->package->title} telah diarkibkan.",
        ];
    }
}
