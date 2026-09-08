<x-admin::layouts>
    <x-slot:title>
        @lang('admin::app.customers.complaint-book.index.title')
    </x-slot>

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('admin::app.customers.complaint-book.index.title')
        </p>

        <x-admin::datagrid.export :src="route('admin.customers.complaint_book.index')" />
    </div>

    {!! view_render_event('bagisto.admin.customers.complaint_book.list.before') !!}

    <x-admin::datagrid :src="route('admin.customers.complaint_book.index')" />

    {!! view_render_event('bagisto.admin.customers.complaint_book.list.after') !!}
</x-admin::layouts>
