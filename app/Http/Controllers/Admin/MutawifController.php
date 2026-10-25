<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MutawifRequest;
use App\Models\Mutawif;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MutawifController extends Controller
{
    public function index(): View
    {
        return view('admin.mutawifs.index', [
            'mutawifs' => Mutawif::query()->orderBy('sort_order')->latest('id')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.mutawifs.create', ['mutawif' => new Mutawif]);
    }

    public function store(MutawifRequest $request): RedirectResponse
    {
        $mutawif = new Mutawif;
        $this->save($request, $mutawif);

        return redirect()->route('admin.mutawifs.index')->with('success', 'Profil mutawif berjaya ditambah.');
    }

    public function show(Mutawif $mutawif): RedirectResponse
    {
        return redirect()->route('admin.mutawifs.edit', $mutawif);
    }

    public function edit(Mutawif $mutawif): View
    {
        return view('admin.mutawifs.edit', compact('mutawif'));
    }

    public function update(MutawifRequest $request, Mutawif $mutawif): RedirectResponse
    {
        $this->save($request, $mutawif);

        return redirect()->route('admin.mutawifs.index')->with('success', 'Profil mutawif berjaya dikemas kini.');
    }

    public function destroy(Mutawif $mutawif): RedirectResponse
    {
        $this->deletePhoto($mutawif->photo);
        $mutawif->delete();

        return back()->with('success', 'Profil mutawif berjaya dipadamkan.');
    }

    private function save(MutawifRequest $request, Mutawif $mutawif): void
    {
        $values = $request->safe()->except(['photo', 'remove_photo', 'is_published', 'sort_order']);

        if (! $mutawif->exists) {
            $values['sort_order'] = ((int) Mutawif::query()->max('sort_order')) + 1;
        }

        $values['is_published'] = $request->boolean('is_published');
        $oldPhoto = $mutawif->photo;

        if ($request->hasFile('photo')) {
            $values['photo'] = $request->file('photo')->store('mutawifs', 'public');
        } elseif ($request->boolean('remove_photo')) {
            $values['photo'] = null;
        }

        $mutawif->fill($values);
        $mutawif->save();

        if ($oldPhoto !== $mutawif->photo) {
            $this->deletePhoto($oldPhoto);
        }
    }

    private function deletePhoto(?string $path): void
    {
        if (filled($path) && ! Str::startsWith($path, ['http://', 'https://'])) {
            Storage::disk('public')->delete($path);
        }
    }
}
