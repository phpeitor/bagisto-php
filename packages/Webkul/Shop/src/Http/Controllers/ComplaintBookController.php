<?php

namespace Webkul\Shop\Http\Controllers;

use App\Models\ComplaintBookEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Webkul\Shop\Http\Requests\ComplaintBookRequest;

class ComplaintBookController extends Controller
{
    /**
     * Loads the complaint book form for the storefront.
     *
     * @return View
     */
    public function create()
    {
        return view('shop::complaint-book.create');
    }

    /**
     * Stores a new complaint book entry.
     *
     * @return RedirectResponse
     */
    public function store(ComplaintBookRequest $complaintBookRequest)
    {
        $data = $complaintBookRequest->only([
            'document_type',
            'document_number',
            'last_name',
            'first_name',
            'address',
            'phone',
            'email',
            'good_type',
            'good_description',
            'claimed_amount',
            'type',
            'detail',
            'request',
        ]);

        $data['correlative'] = ComplaintBookEntry::nextCorrelative();

        $entry = ComplaintBookEntry::create($data);

        session()->flash('success', trans('shop::app.complaint-book.create.success', [
            'correlative' => $entry->correlative,
        ]));

        return redirect()->route('shop.complaint_book.create');
    }

    /**
     * Looks up a consumer's full name by their document number, to autofill the form.
     *
     * @return JsonResponse
     */
    public function lookupDocument(Request $request)
    {
        $request->validate([
            'document_number' => 'required|string|max:20',
        ]);

        $apiUrl = config('services.dni.api_url');

        if (! $apiUrl) {
            return response()->json([
                'success' => false,
                'message' => trans('shop::app.complaint-book.create.lookup-unavailable'),
            ]);
        }

        try {
            $response = Http::timeout(6)->get($apiUrl.$request->input('document_number'));
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => trans('shop::app.complaint-book.create.lookup-failed'),
            ]);
        }

        $result = $response->json();

        if (! $response->successful() || empty($result['success']) || empty($result['name'])) {
            return response()->json([
                'success' => false,
                'message' => trans('shop::app.complaint-book.create.lookup-not-found'),
            ]);
        }

        $words = preg_split('/\s+/', trim($result['name']));

        return response()->json([
            'success' => true,
            'last_name' => implode(' ', array_slice($words, 0, 2)),
            'first_name' => implode(' ', array_slice($words, 2)),
        ]);
    }
}
