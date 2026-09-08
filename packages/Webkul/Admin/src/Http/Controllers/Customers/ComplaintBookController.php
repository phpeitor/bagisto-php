<?php

namespace Webkul\Admin\Http\Controllers\Customers;

use App\Models\ComplaintBookEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Webkul\Admin\DataGrids\Customers\ComplaintBookDataGrid;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Shop\Mail\ComplaintBookResolved;

class ComplaintBookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(ComplaintBookDataGrid::class)->process();
        }

        return view('admin::customers.complaint-book.index');
    }

    /**
     * Show a single complaint book entry.
     *
     * @return View
     */
    public function view(int $id)
    {
        $complaint = ComplaintBookEntry::findOrFail($id);

        return view('admin::customers.complaint-book.view', compact('complaint'));
    }

    /**
     * Update the status and internal notes of a complaint book entry.
     *
     * @return RedirectResponse
     */
    public function update(int $id)
    {
        request()->validate([
            'status' => 'required|in:pendiente,atendido',
            'admin_notes' => 'nullable|string',
        ]);

        $complaint = ComplaintBookEntry::findOrFail($id);

        $wasAttended = $complaint->status === 'atendido';

        $complaint->status = request()->input('status');
        $complaint->admin_notes = request()->input('admin_notes');

        if ($complaint->status === 'atendido' && ! $wasAttended) {
            $complaint->attended_at = now();

            try {
                Mail::queue(new ComplaintBookResolved($complaint));
            } catch (\Exception $e) {
                report($e);
            }
        }

        $complaint->save();

        session()->flash('success', trans('admin::app.customers.complaint-book.view.update-success'));

        return redirect()->route('admin.customers.complaint_book.view', $id);
    }
}
