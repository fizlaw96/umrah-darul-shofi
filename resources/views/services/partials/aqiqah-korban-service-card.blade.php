<article class="aqiqah-korban-service-card">
    <div class="aqiqah-korban-service-card__visual">
        @if ($service->imageUrl())
            <x-responsive-image :src="$service->imageUrl()" sizes="(min-width: 1024px) 33vw, 100vw" alt="{{ $service->name }}" />
        @else
            <span class="material-symbols-outlined">{{ $service->category === \App\Models\AqiqahKorbanService::Aqiqah ? 'pets' : 'agriculture' }}</span>
        @endif
        <span class="aqiqah-korban-service-card__category">{{ $service->category }}</span>
        @if ($badge = $service->promotionBadge())
            <span class="aqiqah-korban-service-card__badge is-{{ $badge['type'] }}"><span class="material-symbols-outlined">{{ $badge['icon'] }}</span>{{ $badge['label'] }}</span>
        @endif
    </div>
    <div class="aqiqah-korban-service-card__body">
        <h3>{{ $service->name }}</h3>
        @if ($service->full_description)
            <p class="aqiqah-korban-service-card__description">{{ \Illuminate\Support\Str::limit($service->full_description, 180) }}</p>
        @endif
        <dl class="aqiqah-korban-service-card__details">
            <div><dt><span class="material-symbols-outlined">pets</span>Jenis haiwan</dt><dd>{{ $service->animal_type }}</dd></div>
            <div><dt><span class="material-symbols-outlined">grid_view</span>Kuantiti</dt><dd>{{ $service->quantity_description }}</dd></div>
            <div><dt><span class="material-symbols-outlined">location_on</span>Lokasi</dt><dd>{{ $service->service_location ?: 'Akan disahkan' }}</dd></div>
        </dl>
        @php($seasonDetails = collect([$service->korban_season, $service->registrationWindowLabel(), $service->registration_period])->filter())
        @if ($seasonDetails->isNotEmpty())
            <p class="aqiqah-korban-service-card__season"><span class="material-symbols-outlined">event</span>{{ $seasonDetails->implode(' · ') }}</p>
        @endif
        <div class="aqiqah-korban-service-card__price"><span>{{ $service->price === null ? 'Harga atas pengesahan' : 'Harga semasa' }}</span><strong>{{ $service->priceLabel() }}</strong></div>
        <div class="aqiqah-korban-service-card__actions">
            <button type="button" data-aqiqah-korban-select-service data-category="{{ $service->category }}" data-service-id="{{ $service->id }}">Pilih servis<span class="material-symbols-outlined">arrow_forward</span></button>
        </div>
    </div>
</article>
