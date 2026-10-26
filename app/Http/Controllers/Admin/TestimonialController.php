<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TestimonialRequest;
use App\Models\Package;
use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    public function index(): View
    {
        return view('admin.testimonials.index', [
            'testimonials' => Testimonial::query()->with('package')->orderBy('sort_order')->latest('id')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.testimonials.create', $this->formData(new Testimonial));
    }

    public function store(TestimonialRequest $request): RedirectResponse
    {
        $testimonial = new Testimonial;
        $this->save($request, $testimonial);

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimoni berjaya ditambah.');
    }

    public function show(Testimonial $testimonial): RedirectResponse
    {
        return redirect()->route('admin.testimonials.edit', $testimonial);
    }

    public function edit(Testimonial $testimonial): View
    {
        return view('admin.testimonials.edit', $this->formData($testimonial));
    }

    public function update(TestimonialRequest $request, Testimonial $testimonial): RedirectResponse
    {
        $this->save($request, $testimonial);

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimoni berjaya dikemas kini.');
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        $this->deletePhoto($testimonial->photo);
        $testimonial->delete();

        return back()->with('success', 'Testimoni berjaya dipadamkan.');
    }

    /** @return array{testimonial: Testimonial, packages: Collection<int, Package>} */
    private function formData(Testimonial $testimonial): array
    {
        return [
            'testimonial' => $testimonial,
            'packages' => Package::query()->orderBy('title')->get(['id', 'title']),
        ];
    }

    private function save(TestimonialRequest $request, Testimonial $testimonial): void
    {
        $values = $request->safe()->except(['photo', 'remove_photo', 'is_published', 'sort_order']);

        if (! $testimonial->exists) {
            $values['sort_order'] = ((int) Testimonial::query()->max('sort_order')) + 1;
        }

        $values['is_published'] = $request->boolean('is_published');
        $oldPhoto = $testimonial->photo;

        if ($request->hasFile('photo')) {
            $values['photo'] = $request->file('photo')->store('testimonials', 'public');
        } elseif ($request->boolean('remove_photo')) {
            $values['photo'] = null;
        }

        DB::transaction(function () use ($testimonial, $values): void {
            $testimonial->fill($values);
            $testimonial->save();

            if ($testimonial->is_published) {
                $this->unpublishOldestExcessTestimonials($testimonial);
            }
        });

        if ($oldPhoto !== $testimonial->photo) {
            $this->deletePhoto($oldPhoto);
        }
    }

    private function unpublishOldestExcessTestimonials(Testimonial $testimonial): void
    {
        $publishedOtherTestimonials = Testimonial::query()
            ->published()
            ->whereKeyNot($testimonial);

        $numberToUnpublish = $publishedOtherTestimonials->count() - (Testimonial::MaximumPublished - 1);

        if ($numberToUnpublish <= 0) {
            return;
        }

        $testimonialIds = $publishedOtherTestimonials
            ->oldest()
            ->oldest('id')
            ->limit($numberToUnpublish)
            ->pluck('id');

        Testimonial::query()
            ->whereKey($testimonialIds)
            ->update(['is_published' => false]);
    }

    private function deletePhoto(?string $path): void
    {
        if (filled($path) && ! Str::startsWith($path, ['http://', 'https://'])) {
            Storage::disk('public')->delete($path);
        }
    }
}
