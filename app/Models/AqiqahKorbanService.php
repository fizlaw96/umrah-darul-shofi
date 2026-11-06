<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\AqiqahKorbanServiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['category', 'name', 'animal_type', 'quantity_description', 'service_location', 'price', 'full_description', 'featured_image', 'is_published', 'promotion_badge', 'sort_order', 'korban_season', 'registration_opens_on', 'registration_closes_on', 'registration_period', 'additional_terms'])]
class AqiqahKorbanService extends Model
{
    public const Aqiqah = 'Aqiqah';

    public const Korban = 'Korban';

    public const RecommendationBadge = 'recommendation';

    public const SuperDealBadge = 'super_deal';

    /**
     * Number of korban shares (bahagian) in one large animal.
     */
    public const SharesPerLargeAnimal = 7;

    /** @use HasFactory<AqiqahKorbanServiceFactory> */
    use HasFactory;

    /**
     * @return array<int, string>
     */
    public static function categories(): array
    {
        return [self::Aqiqah, self::Korban];
    }

    /**
     * @return array<int, string>
     */
    public static function animalTypes(): array
    {
        return ['Kambing', 'Biri-biri', 'Lembu', 'Kerbau', 'Unta'];
    }

    /**
     * @return array<int, string>
     */
    public static function quantityDescriptions(): array
    {
        return ['1 Ekor', '2 Ekor', '1 Bahagian', '2 Bahagian', '3 Bahagian', '4 Bahagian', '5 Bahagian', '6 Bahagian', '7 Bahagian'];
    }

    /**
     * @return array<string, array{label: string, icon: string}>
     */
    public static function promotionBadges(): array
    {
        return [
            self::RecommendationBadge => [
                'label' => 'Recommended',
                'icon' => 'star',
            ],
            self::SuperDealBadge => [
                'label' => 'Super Deal',
                'icon' => 'local_fire_department',
            ],
        ];
    }

    /**
     * @return array{label: string, icon: string, type: string}|null
     */
    public function promotionBadge(): ?array
    {
        $badge = static::promotionBadges()[$this->promotion_badge] ?? null;

        return $badge === null ? null : [...$badge, 'type' => $this->promotion_badge];
    }

    /**
     * @return array<int, string>
     */
    public static function largeAnimalTypes(): array
    {
        return ['Lembu', 'Kerbau', 'Unta'];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * Services whose registration window (if any) includes today.
     */
    public function scopeOpenForRegistration(Builder $query, ?CarbonInterface $today = null): Builder
    {
        $date = ($today ?? today())->toDateString();

        return $query
            ->where(fn (Builder $query): Builder => $query->whereNull('registration_opens_on')->orWhereDate('registration_opens_on', '<=', $date))
            ->where(fn (Builder $query): Builder => $query->whereNull('registration_closes_on')->orWhereDate('registration_closes_on', '>=', $date));
    }

    /**
     * Scope for the order staff choose: lower sort order first, newest first on ties.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->latest('id');
    }

    public function isOpenForRegistration(?CarbonInterface $today = null): bool
    {
        $date = ($today ?? today())->startOfDay();

        return ($this->registration_opens_on === null || $this->registration_opens_on->lte($date))
            && ($this->registration_closes_on === null || $this->registration_closes_on->gte($date));
    }

    public function registrationWindowLabel(): ?string
    {
        if ($this->registration_opens_on === null && $this->registration_closes_on === null) {
            return null;
        }

        $format = fn (Carbon $date): string => $date->locale('ms')->translatedFormat('j M Y');

        return match (true) {
            $this->registration_opens_on !== null && $this->registration_closes_on !== null => 'Pendaftaran: '.$format($this->registration_opens_on).' – '.$format($this->registration_closes_on),
            $this->registration_closes_on !== null => 'Pendaftaran ditutup '.$format($this->registration_closes_on),
            default => 'Pendaftaran dibuka '.$format($this->registration_opens_on),
        };
    }

    /**
     * How many participant names one unit of this package covers.
     */
    public function participantSlotsPerUnit(): int
    {
        $count = max(1, (int) $this->quantity_description);

        if (str_contains((string) $this->quantity_description, 'Ekor') && in_array($this->animal_type, static::largeAnimalTypes(), true)) {
            return $count * static::SharesPerLargeAnimal;
        }

        return $count;
    }

    public function imageUrl(): ?string
    {
        if (blank($this->featured_image)) {
            return null;
        }

        if (Str::startsWith($this->featured_image, ['http://', 'https://'])) {
            return $this->featured_image;
        }

        return Storage::disk('public')->url($this->featured_image);
    }

    public function priceLabel(): string
    {
        if ($this->price === null) {
            return 'Hubungi Untuk Harga';
        }

        return static::formatRinggit((float) $this->price);
    }

    public function estimatedTotalLabel(int $quantity): ?string
    {
        if ($this->price === null) {
            return null;
        }

        return static::formatRinggit((float) $this->price * $quantity);
    }

    private static function formatRinggit(float $amount): string
    {
        return 'RM '.number_format($amount, 2, '.', ',');
    }

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_published' => 'boolean',
            'registration_opens_on' => 'date',
            'registration_closes_on' => 'date',
        ];
    }
}
