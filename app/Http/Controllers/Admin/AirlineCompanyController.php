<?php

namespace App\Http\Controllers\Admin;

use App\Actions\PrepareAirlineLogo;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AirlineCompanyRequest;
use App\Models\AirlineCompany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AirlineCompanyController extends Controller
{
    public function __construct(private readonly PrepareAirlineLogo $prepareAirlineLogo) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('admin.airline-companies.index', [
            'airlineCompanies' => AirlineCompany::query()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.airline-companies.create', ['airlineCompany' => new AirlineCompany]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AirlineCompanyRequest $request): RedirectResponse
    {
        $values = $request->safe()->except(['logo', 'is_published']);
        $values['is_published'] = $request->boolean('is_published');
        $values['sort_order'] = $values['sort_order'] ?? ((int) AirlineCompany::query()->max('sort_order')) + 1;
        $values['logo'] = $this->prepareAirlineLogo->handle($request->file('logo'));

        AirlineCompany::query()->create($values);

        return redirect()->route('admin.airline-companies.index')->with('success', 'Syarikat penerbangan berjaya ditambah.');
    }

    /**
     * Display the specified resource.
     */
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AirlineCompany $airlineCompany): View
    {
        return view('admin.airline-companies.edit', compact('airlineCompany'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AirlineCompanyRequest $request, AirlineCompany $airlineCompany): RedirectResponse
    {
        $values = $request->safe()->except(['logo', 'is_published']);
        $values['is_published'] = $request->boolean('is_published');
        $previousLogo = $airlineCompany->logo;

        if ($request->hasFile('logo')) {
            $values['logo'] = $this->prepareAirlineLogo->handle($request->file('logo'));
        }

        $airlineCompany->update($values);

        if ($previousLogo !== $airlineCompany->logo && ! Str::startsWith($previousLogo, ['http://', 'https://'])) {
            Storage::disk('public')->delete($previousLogo);
        }

        return redirect()->route('admin.airline-companies.index')->with('success', 'Syarikat penerbangan berjaya dikemas kini.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AirlineCompany $airlineCompany): RedirectResponse
    {
        $logo = $airlineCompany->logo;
        $airlineCompany->delete();

        if (! Str::startsWith($logo, ['http://', 'https://'])) {
            Storage::disk('public')->delete($logo);
        }

        return back()->with('success', 'Syarikat penerbangan berjaya dipadamkan.');
    }
}
