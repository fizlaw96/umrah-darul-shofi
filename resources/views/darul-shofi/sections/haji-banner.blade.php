<section class="ds-testimonials" id="testimoni">
    <div class="ds-testimonials__inner">
        <div class="ds-testimonials__heading">
            <span>Suara Jemaah</span>
            <h2>Pengalaman Jemaah Bersama Kami</h2>
        </div>

        @if ($testimonials->isNotEmpty())
            <div class="ds-testimonials__list">
                @foreach ($testimonials as $testimonial)
                    <article class="ds-testimonial-card" data-testimonial-card>
                        <span class="ds-testimonial-card__quote" aria-hidden="true">“</span>
                        <p class="ds-testimonial-card__review">{{ $testimonial->review }}</p>
                        <footer class="ds-testimonial-card__footer">
                            <div class="ds-testimonial-card__person">
                                @if ($testimonial->photoUrl())
                                    <img class="ds-testimonial-card__photo" src="{{ $testimonial->photoUrl() }}" alt="Foto {{ $testimonial->reviewer_name }}" style="object-position: {{ $imageFocusPositions[$testimonial->photo] ?? '50% 50%' }}" loading="lazy">
                                @else
                                    <span class="ds-testimonial-card__photo-fallback" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($testimonial->reviewer_name, 0, 1)) }}</span>
                                @endif
                                <div class="ds-testimonial-card__identity">
                                    <h3>{{ $testimonial->reviewer_name }}</h3>
                                    @if ($testimonial->package)
                                        <p>{{ $testimonial->package->title }}</p>
                                    @endif
                                </div>
                            </div>
                            <p class="ds-testimonial-card__rating" aria-label="{{ $testimonial->rating }} daripada 5 bintang">
                                {{ str_repeat('★', $testimonial->rating) }}{{ str_repeat('☆', 5 - $testimonial->rating) }}
                            </p>
                        </footer>
                    </article>
                @endforeach
            </div>
        @else
            <p class="ds-testimonials__empty">Testimoni akan dipaparkan selepas disemak dan diterbitkan oleh pihak agensi.</p>
        @endif
    </div>
</section>
