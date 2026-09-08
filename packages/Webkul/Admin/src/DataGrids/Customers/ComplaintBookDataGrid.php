<?php

namespace Webkul\Admin\DataGrids\Customers;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class ComplaintBookDataGrid extends DataGrid
{
    /**
     * Prepare query builder.
     *
     * @return Builder
     */
    public function prepareQueryBuilder()
    {
        $queryBuilder = DB::table('complaint_book_entries')
            ->addSelect(
                'id',
                'correlative',
                DB::raw("CONCAT(last_name, ', ', first_name) as customer_name"),
                'document_number',
                'type',
                'good_type',
                'good_description',
                'status',
                'created_at'
            );

        $this->addFilter('id', 'complaint_book_entries.id');
        $this->addFilter('correlative', 'complaint_book_entries.correlative');
        $this->addFilter('document_number', 'complaint_book_entries.document_number');
        $this->addFilter('type', 'complaint_book_entries.type');
        $this->addFilter('good_type', 'complaint_book_entries.good_type');
        $this->addFilter('status', 'complaint_book_entries.status');
        $this->addFilter('created_at', 'complaint_book_entries.created_at');
        $this->addFilter('customer_name', DB::raw("CONCAT(last_name, ', ', first_name)"));

        return $queryBuilder;
    }

    /**
     * Add columns.
     *
     * @return void
     */
    public function prepareColumns()
    {
        $this->addColumn([
            'index' => 'correlative',
            'label' => trans('admin::app.customers.complaint-book.index.datagrid.correlative'),
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'customer_name',
            'label' => trans('admin::app.customers.complaint-book.index.datagrid.customer-name'),
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'document_number',
            'label' => trans('admin::app.customers.complaint-book.index.datagrid.document-number'),
            'type' => 'string',
            'searchable' => true,
            'sortable' => true,
            'filterable' => true,
        ]);

        $this->addColumn([
            'index' => 'type',
            'label' => trans('admin::app.customers.complaint-book.index.datagrid.type'),
            'type' => 'string',
            'sortable' => true,
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                [
                    'label' => trans('admin::app.customers.complaint-book.index.datagrid.claim'),
                    'value' => 'reclamo',
                ],
                [
                    'label' => trans('admin::app.customers.complaint-book.index.datagrid.complaint'),
                    'value' => 'queja',
                ],
            ],
            'closure' => function ($row) {
                return $row->type === 'reclamo'
                    ? trans('admin::app.customers.complaint-book.index.datagrid.claim')
                    : trans('admin::app.customers.complaint-book.index.datagrid.complaint');
            },
        ]);

        $this->addColumn([
            'index' => 'good_type',
            'label' => trans('admin::app.customers.complaint-book.index.datagrid.good-type'),
            'type' => 'string',
            'sortable' => true,
            'searchable' => false,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                [
                    'label' => trans('admin::app.customers.complaint-book.index.datagrid.product'),
                    'value' => 'producto',
                ],
                [
                    'label' => trans('admin::app.customers.complaint-book.index.datagrid.service'),
                    'value' => 'servicio',
                ],
            ],
            'closure' => function ($row) {
                return $row->good_type === 'producto'
                    ? trans('admin::app.customers.complaint-book.index.datagrid.product')
                    : trans('admin::app.customers.complaint-book.index.datagrid.service');
            },
        ]);

        $this->addColumn([
            'index' => 'good_description',
            'label' => trans('admin::app.customers.complaint-book.index.datagrid.good-description'),
            'type' => 'string',
            'sortable' => false,
            'searchable' => true,
            'filterable' => false,
        ]);

        $this->addColumn([
            'index' => 'status',
            'label' => trans('admin::app.customers.complaint-book.index.datagrid.status'),
            'type' => 'string',
            'searchable' => false,
            'sortable' => true,
            'filterable' => true,
            'filterable_type' => 'dropdown',
            'filterable_options' => [
                [
                    'label' => trans('admin::app.customers.complaint-book.index.datagrid.pending'),
                    'value' => 'pendiente',
                ],
                [
                    'label' => trans('admin::app.customers.complaint-book.index.datagrid.attended'),
                    'value' => 'atendido',
                ],
            ],
            'closure' => function ($row) {
                return $row->status === 'atendido'
                    ? '<p class="label-active">'.trans('admin::app.customers.complaint-book.index.datagrid.attended').'</p>'
                    : '<p class="label-pending">'.trans('admin::app.customers.complaint-book.index.datagrid.pending').'</p>';
            },
        ]);

        $this->addColumn([
            'index' => 'created_at',
            'label' => trans('admin::app.customers.complaint-book.index.datagrid.created-at'),
            'type' => 'date',
            'filterable' => true,
            'filterable_type' => 'date_range',
            'sortable' => true,
        ]);
    }

    /**
     * Add actions.
     *
     * @return void
     */
    public function prepareActions()
    {
        if (bouncer()->hasPermission('customers.complaint_book')) {
            $this->addAction([
                'index' => 'view',
                'icon' => 'icon-view',
                'title' => trans('admin::app.customers.complaint-book.index.datagrid.view'),
                'method' => 'GET',
                'url' => function ($row) {
                    return route('admin.customers.complaint_book.view', $row->id);
                },
            ]);
        }
    }
}
