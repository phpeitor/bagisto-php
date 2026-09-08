<?php

namespace Webkul\Admin\Http\Controllers\Customers;

use App\Models\ComplaintBookEntry;
use Illuminate\View\View;
use Webkul\Admin\DataGrids\Customers\ComplaintBookDataGrid;
use Webkul\Admin\Http\Controllers\Controller;

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
}
