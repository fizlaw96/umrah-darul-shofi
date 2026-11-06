<?php

namespace App\Models;

use Database\Factories\ServiceEnquiryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['service_type', 'aqiqah_korban_service_id', 'status', 'full_name', 'contact_number', 'summary', 'details', 'message', 'staff_notes', 'contacted_at'])]
class ServiceEnquiry extends Model
{
    public const TransportType = 'transport';

    public const AqiqahKorbanType = 'aqiqah_korban';

    public const NewStatus = 'new';

    public const ContactedStatus = 'contacted';

    public const CompletedStatus = 'completed';

    public const CancelledStatus = 'cancelled';

    /** @use HasFactory<ServiceEnquiryFactory> */
    use HasFactory;

    /**
     * @var array<string, string>
     */
    protected $attributes = [
        'status' => self::NewStatus,
    ];

    /**
     * @return array<string, string>
     */
    public static function serviceTypes(): array
    {
        return [
            self::TransportType => 'Transport',
            self::AqiqahKorbanType => 'Aqiqah & Korban',
        ];
    }

    /**
     * @return array<string, array{label: string, icon: string}>
     */
    public static function statuses(): array
    {
        return [
            self::NewStatus => ['label' => 'Baru', 'icon' => 'fiber_new'],
            self::ContactedStatus => ['label' => 'Dihubungi', 'icon' => 'call'],
            self::CompletedStatus => ['label' => 'Selesai', 'icon' => 'task_alt'],
            self::CancelledStatus => ['label' => 'Batal', 'icon' => 'cancel'],
        ];
    }

    public function aqiqahKorbanService(): BelongsTo
    {
        return $this->belongsTo(AqiqahKorbanService::class);
    }

    public function scopeOfType(Builder $query, string $serviceType): Builder
    {
        return $query->where('service_type', $serviceType);
    }

    public function scopeWithStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function serviceTypeLabel(): string
    {
        return static::serviceTypes()[$this->service_type] ?? $this->service_type;
    }

    public function statusLabel(): string
    {
        return static::statuses()[$this->status]['label'] ?? $this->status;
    }

    public function statusIcon(): string
    {
        return static::statuses()[$this->status]['icon'] ?? 'help';
    }

    /**
     * Link for staff to open a WhatsApp chat with the customer.
     */
    public function customerWhatsAppUrl(): ?string
    {
        $number = WebsiteSetting::normalizedWhatsAppNumber($this->contact_number);

        return $number === null ? null : 'https://wa.me/'.$number;
    }

    protected function casts(): array
    {
        return [
            'details' => 'array',
            'contacted_at' => 'datetime',
        ];
    }
}
