<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EnquiryStatusRequest;
use App\Models\Enquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnquiryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $enquiries = Enquiry::query()
            ->with('package')
            ->when($request->filled('q'), function ($query) use ($request): void {
                $search = '%'.$request->string('q')->toString().'%';
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->where('name', 'like', $search)->orWhere('phone', 'like', $search);
                });
            })
            ->when($request->filled('status'), function ($query) use ($request): void {
                $query->where('status', $request->string('status')->toString());
            })
            ->when($request->filled('date'), function ($query) use ($request): void {
                $query->whereDate('created_at', $request->date('date'));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.enquiries.index', compact('enquiries'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): RedirectResponse
    {
        return redirect()->route('admin.enquiries.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        abort(405);
    }

    /**
     * Display the specified resource.
     */
    public function show(Enquiry $enquiry): View
    {
        $enquiry->load('package');

        return view('admin.enquiries.show', compact('enquiry'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Enquiry $enquiry): RedirectResponse
    {
        return redirect()->route('admin.enquiries.show', $enquiry);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EnquiryStatusRequest $request, Enquiry $enquiry): RedirectResponse
    {
        $enquiry->update($request->safe()->only(['status', 'internal_notes']));

        return back()->with('success', 'Pertanyaan berjaya dikemas kini.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Enquiry $enquiry): RedirectResponse
    {
        abort(405);
    }
}
