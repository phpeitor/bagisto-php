<x-admin::layouts>
    <x-slot:title>
        {{ $complaint->correlative }} — @lang('admin::app.customers.complaint-book.view.title')
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <div class="flex items-center gap-2.5">
            <h1 class="text-xl font-bold leading-6 text-gray-800 dark:text-white">
                {{ $complaint->correlative }}
            </h1>

            @if ($complaint->status === 'atendido')
                <span class="label-active mx-1.5 text-sm">
                    @lang('admin::app.customers.complaint-book.index.datagrid.attended')
                </span>
            @else
                <span class="label-pending mx-1.5 text-sm">
                    @lang('admin::app.customers.complaint-book.index.datagrid.pending')
                </span>
            @endif

            <span class="label-processing mx-1.5 text-sm">
                {{ $complaint->type === 'reclamo'
                    ? trans('admin::app.customers.complaint-book.index.datagrid.claim')
                    : trans('admin::app.customers.complaint-book.index.datagrid.complaint') }}
            </span>
        </div>

        <!-- Back Button -->
        <a
            href="{{ route('admin.customers.complaint_book.index') }}"
            class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
        >
            @lang('admin::app.customers.complaint-book.view.back-btn')
        </a>
    </div>

    <div class="mt-4 flex gap-2.5 max-lg:flex-wrap">
        <!-- Left Component -->
        <div class="flex flex-1 flex-col gap-2 max-lg:w-full">
            <!-- Good / Service -->
            <x-admin::accordion>
                <x-slot:header>
                    <p class="w-full p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('admin::app.customers.complaint-book.view.good-section')
                    </p>
                </x-slot:header>

                <x-slot:content>
                    <div class="grid grid-cols-2 gap-4 max-sm:grid-cols-1">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                @lang('admin::app.customers.complaint-book.index.datagrid.good-type')
                            </p>

                            <p class="font-semibold text-gray-800 dark:text-white">
                                {{ $complaint->good_type === 'producto'
                                    ? trans('admin::app.customers.complaint-book.index.datagrid.product')
                                    : trans('admin::app.customers.complaint-book.index.datagrid.service') }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                @lang('admin::app.customers.complaint-book.view.claimed-amount')
                            </p>

                            <p class="font-semibold text-gray-800 dark:text-white">
                                {{ $complaint->claimed_amount !== null ? 'S/ '.number_format($complaint->claimed_amount, 2) : '—' }}
                            </p>
                        </div>

                        <div class="col-span-2">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                @lang('admin::app.customers.complaint-book.index.datagrid.good-description')
                            </p>

                            <p class="font-semibold text-gray-800 dark:text-white">
                                {{ $complaint->good_description }}
                            </p>
                        </div>
                    </div>
                </x-slot:content>
            </x-admin::accordion>

            <!-- Detail / Request -->
            <x-admin::accordion>
                <x-slot:header>
                    <p class="w-full p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('admin::app.customers.complaint-book.view.claim-section')
                    </p>
                </x-slot:header>

                <x-slot:content>
                    <div class="grid gap-4">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                @lang('admin::app.customers.complaint-book.view.detail')
                            </p>

                            <p class="whitespace-pre-line font-semibold text-gray-800 dark:text-white">
                                {{ $complaint->detail }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                @lang('admin::app.customers.complaint-book.view.request')
                            </p>

                            <p class="whitespace-pre-line font-semibold text-gray-800 dark:text-white">
                                {{ $complaint->request }}
                            </p>
                        </div>
                    </div>
                </x-slot:content>
            </x-admin::accordion>
        </div>

        <!-- Right Component -->
        <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">
            <x-admin::accordion>
                <x-slot:header>
                    <p class="w-full p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('admin::app.customers.complaint-book.view.consumer-section')
                    </p>
                </x-slot:header>

                <x-slot:content>
                    <div class="grid gap-y-2.5">
                        <p class="break-all font-semibold text-gray-800 dark:text-white">
                            {{ $complaint->last_name }}, {{ $complaint->first_name }}
                        </p>

                        <p class="text-gray-600 dark:text-gray-300">
                            {{ strtoupper($complaint->document_type) }}: {{ $complaint->document_number }}
                        </p>

                        <p class="text-gray-600 dark:text-gray-300">
                            {{ $complaint->address }}
                        </p>

                        <p class="text-gray-600 dark:text-gray-300">
                            {{ $complaint->email }}
                        </p>

                        @if ($complaint->phone)
                            <p class="text-gray-600 dark:text-gray-300">
                                {{ $complaint->phone }}
                            </p>
                        @endif

                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            @lang('admin::app.customers.complaint-book.index.datagrid.created-at'): {{ $complaint->created_at }}
                        </p>
                    </div>
                </x-slot:content>
            </x-admin::accordion>
        </div>
    </div>
</x-admin::layouts>
