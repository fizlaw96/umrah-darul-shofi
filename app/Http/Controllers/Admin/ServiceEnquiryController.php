<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServiceEnquiryRequest;
use App\Models\ServiceEnquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceEnquiryController extends Controller
{
    public function index(Request $request): View
    {
        $serviceType = $request->string('type')->toString();
        $status = $request->string('status')->toString();
        $serviceType = array_key_exists($serviceType, ServiceEnquiry::serviceTypes()) ? $serviceType : null;
        $status = array_key_exists($status, ServiceEnquiry::statuses()) ? $status : null;

        $enquiries = ServiceEnquiry::query()
            ->when($serviceType !== null, fn ($query) => $query->ofType($serviceType))
            ->when($status !== null, fn ($query) => $query->withStatus($status))
            ->when($request->filled('q'), function ($query) use ($request): void {
                $search = '%'.$request->string('q')->toString().'%';

                $query->where(function ($enquiryQuery) use ($search): void {
                    $enquiryQuery
                        ->where('full_name', 'like', $search)
                        ->orWhere('contact_number', 'like', $search)
                        ->orWhere('summary', 'like', $search);
                });
            })
            ->latest()
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $statusCounts = ServiceEnquiry::query()
            ->when($serviceType !== null, fn ($query) => $query->ofType($serviceType))
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return view('admin.service-enquiries.index', [
            'enquiries' => $enquiries,
            'statusCounts' => $statusCounts,
            'selectedServiceType' => $serviceType,
            'selectedStatus' => $status,
        ]);
    }

    public function show(ServiceEnquiry $serviceEnquiry): View
    {
        $serviceEnquiry->load('aqiqahKorbanService');

        return view('admin.service-enquiries.show', [
            'enquiry' => $serviceEnquiry,
        ]);
    }

    public function update(ServiceEnquiryRequest $request, ServiceEnquiry $serviceEnquiry): RedirectResponse
    {
        $serviceEnquiry->fill($request->validated());

        if ($serviceEnquiry->status !== ServiceEnquiry::NewStatus && $serviceEnquiry->contacted_at === null) {
            $serviceEnquiry->contacted_at = now();
        }

        $serviceEnquiry->save();

        return redirect()->route('admin.service-enquiries.show', $serviceEnquiry)->with('success', 'Status permintaan berjaya dikemas kini.');
    }

    public function destroy(ServiceEnquiry $serviceEnquiry): RedirectResponse
    {
        $serviceEnquiry->delete();

        return redirect()->route('admin.service-enquiries.index')->with('success', 'Permintaan berjaya dipadamkan.');
    }
}
