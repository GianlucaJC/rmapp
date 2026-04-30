<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ServiceRequestController extends Controller
{
    /**
     * Process DataTables AJAX requests for service requests.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getServiceRequestsData(Request $request)
    {
        if ($request->ajax()) {
            $data = ServiceRequest::with('user'); // Eager load the user relationship

            // Apply filters
            if ($request->filled('status')) {
                $data->where('status', $request->status);
            }
            if ($request->filled('service_type')) {
                $data->where('service_type', $request->service_type);
            }

            try {
                return DataTables::of($data)
                    ->addIndexColumn()
                    ->addColumn('user_name', function(ServiceRequest $serviceRequest) {
                        return $serviceRequest->user ? $serviceRequest->user->name : 'N/A';
                    })
                    ->addColumn('created_at', function(ServiceRequest $serviceRequest) {
                        return $serviceRequest->created_at->format('d/m/Y H:i');
                    })
                    ->addColumn('actions', function(ServiceRequest $serviceRequest) {
                        $detailUrl = route('admin.service-requests.show', $serviceRequest->id);
                        return '<a href="'.$detailUrl.'" class="btn btn-info btn-sm me-1"><i class="bi bi-eye"></i> Dettagli</a>';
                    })
                    ->rawColumns(['actions'])
                    ->make(true);
            } catch (\Exception $e) {
                \Log::error('DataTables AJAX Error: ' . $e->getMessage(), ['exception' => $e]);
                return response()->json(['error' => 'Si è verificato un errore interno durante il recupero dei dati. Controlla i log del server per i dettagli.'], 500);
            }
        }
    }

    /**
     * Show the details of a specific service request.
     *
     * @param  \App\Models\ServiceRequest  $serviceRequest
     * @return \Illuminate\View\View
     */
    public function show(ServiceRequest $serviceRequest)
    {
        return view('admin.service_requests.show', compact('serviceRequest'));
    }
}