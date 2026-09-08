<!-- Page Layout -->
<x-shop::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('shop::app.complaint-book.create.title')
    </x-slot:title>

    <div class="container mt-8 max-1180:px-5 max-md:mt-6 max-md:px-4">
        <div class="m-auto w-full max-w-[870px] rounded-xl border border-zinc-200 p-16 px-[90px] max-md:px-8 max-md:py-8 max-sm:border-none max-sm:p-0">
            <h1 class="font-dmserif text-4xl max-md:text-3xl max-sm:text-xl">
                @lang('shop::app.complaint-book.create.title')
            </h1>

            <p class="mt-4 text-base text-zinc-500 max-sm:mt-1 max-sm:text-sm">
                @lang('shop::app.complaint-book.create.subtitle')
            </p>

            <p class="mt-3 text-sm font-medium text-navyBlue">
                @lang('shop::app.complaint-book.create.response-time-notice')
            </p>

            <div class="mt-10 max-sm:mt-6">
                <x-shop::form :action="route('shop.complaint_book.store')" id="complaint-book-form">
                    <!-- 1. Consumer -->
                    <h2 class="mb-5 text-lg font-medium">
                        @lang('shop::app.complaint-book.create.consumer-section')
                    </h2>

                    <!-- Document Type -->
                    <x-shop::form.control-group>
                        <x-shop::form.control-group.label class="required">
                            @lang('shop::app.complaint-book.create.document-type')
                        </x-shop::form.control-group.label>

                        <div class="flex flex-wrap gap-6">
                            <div class="flex items-center gap-2.5">
                                <x-shop::form.control-group.control
                                    type="radio"
                                    id="document_type_dni"
                                    name="document_type"
                                    value="dni"
                                    for="document_type_dni"
                                    rules="required"
                                    ::checked="true"
                                    :label="trans('shop::app.complaint-book.create.document-type')"
                                />

                                <x-shop::form.control-group.label for="document_type_dni" class="!mb-0">
                                    @lang('shop::app.complaint-book.create.document-type-dni')
                                </x-shop::form.control-group.label>
                            </div>

                            <div class="flex items-center gap-2.5">
                                <x-shop::form.control-group.control
                                    type="radio"
                                    id="document_type_ce"
                                    name="document_type"
                                    value="ce"
                                    for="document_type_ce"
                                    rules="required"
                                    :label="trans('shop::app.complaint-book.create.document-type')"
                                />

                                <x-shop::form.control-group.label for="document_type_ce" class="!mb-0">
                                    @lang('shop::app.complaint-book.create.document-type-ce')
                                </x-shop::form.control-group.label>
                            </div>

                            <div class="flex items-center gap-2.5">
                                <x-shop::form.control-group.control
                                    type="radio"
                                    id="document_type_pasaporte"
                                    name="document_type"
                                    value="pasaporte"
                                    for="document_type_pasaporte"
                                    rules="required"
                                    :label="trans('shop::app.complaint-book.create.document-type')"
                                />

                                <x-shop::form.control-group.label for="document_type_pasaporte" class="!mb-0">
                                    @lang('shop::app.complaint-book.create.document-type-passport')
                                </x-shop::form.control-group.label>
                            </div>
                        </div>

                        <x-shop::form.control-group.error control-name="document_type" />
                    </x-shop::form.control-group>

                    <!-- Document Number -->
                    <x-shop::form.control-group>
                        <x-shop::form.control-group.label class="required">
                            @lang('shop::app.complaint-book.create.document-number')
                        </x-shop::form.control-group.label>

                        <x-shop::form.control-group.control
                            type="text"
                            id="document_number"
                            class="px-6 py-5 max-md:py-3 max-sm:py-3.5"
                            name="document_number"
                            rules="required"
                            :value="old('document_number')"
                            :label="trans('shop::app.complaint-book.create.document-number')"
                            :placeholder="trans('shop::app.complaint-book.create.document-number')"
                            maxlength="20"
                        />

                        <p id="document-lookup-message" class="mb-1.5 hidden text-xs"></p>

                        <x-shop::form.control-group.error control-name="document_number" />
                    </x-shop::form.control-group>

                    <div class="grid grid-cols-2 gap-x-5 max-sm:grid-cols-1">
                        <!-- Last Name -->
                        <x-shop::form.control-group>
                            <x-shop::form.control-group.label class="required">
                                @lang('shop::app.complaint-book.create.last-name')
                            </x-shop::form.control-group.label>

                            <x-shop::form.control-group.control
                                type="text"
                                id="last_name"
                                class="px-6 py-5 max-md:py-3 max-sm:py-3.5"
                                name="last_name"
                                rules="required"
                                :value="old('last_name')"
                                :label="trans('shop::app.complaint-book.create.last-name')"
                                :placeholder="trans('shop::app.complaint-book.create.last-name')"
                            />

                            <x-shop::form.control-group.error control-name="last_name" />
                        </x-shop::form.control-group>

                        <!-- First Name -->
                        <x-shop::form.control-group>
                            <x-shop::form.control-group.label class="required">
                                @lang('shop::app.complaint-book.create.first-name')
                            </x-shop::form.control-group.label>

                            <x-shop::form.control-group.control
                                type="text"
                                id="first_name"
                                class="px-6 py-5 max-md:py-3 max-sm:py-3.5"
                                name="first_name"
                                rules="required"
                                :value="old('first_name')"
                                :label="trans('shop::app.complaint-book.create.first-name')"
                                :placeholder="trans('shop::app.complaint-book.create.first-name')"
                            />

                            <x-shop::form.control-group.error control-name="first_name" />
                        </x-shop::form.control-group>
                    </div>

                    <!-- Address -->
                    <x-shop::form.control-group>
                        <x-shop::form.control-group.label class="required">
                            @lang('shop::app.complaint-book.create.address')
                        </x-shop::form.control-group.label>

                        <x-shop::form.control-group.control
                            type="text"
                            class="px-6 py-5 max-md:py-3 max-sm:py-3.5"
                            name="address"
                            rules="required"
                            :value="old('address')"
                            :label="trans('shop::app.complaint-book.create.address')"
                            :placeholder="trans('shop::app.complaint-book.create.address')"
                        />

                        <x-shop::form.control-group.error control-name="address" />
                    </x-shop::form.control-group>

                    <div class="grid grid-cols-2 gap-x-5 max-sm:grid-cols-1">
                        <!-- Phone -->
                        <x-shop::form.control-group>
                            <x-shop::form.control-group.label>
                                @lang('shop::app.complaint-book.create.phone')
                            </x-shop::form.control-group.label>

                            <x-shop::form.control-group.control
                                type="text"
                                class="px-6 py-5 max-md:py-3 max-sm:py-3.5"
                                name="phone"
                                rules="phone"
                                :value="old('phone')"
                                :label="trans('shop::app.complaint-book.create.phone')"
                                :placeholder="trans('shop::app.complaint-book.create.phone')"
                            />

                            <x-shop::form.control-group.error control-name="phone" />
                        </x-shop::form.control-group>

                        <!-- Email -->
                        <x-shop::form.control-group>
                            <x-shop::form.control-group.label class="required">
                                @lang('shop::app.complaint-book.create.email')
                            </x-shop::form.control-group.label>

                            <x-shop::form.control-group.control
                                type="email"
                                class="px-6 py-5 max-md:py-3 max-sm:py-3.5"
                                name="email"
                                rules="required|email"
                                :value="old('email')"
                                :label="trans('shop::app.complaint-book.create.email')"
                                :placeholder="trans('shop::app.complaint-book.create.email')"
                            />

                            <x-shop::form.control-group.error control-name="email" />
                        </x-shop::form.control-group>
                    </div>

                    <!-- 2. Good or service -->
                    <h2 class="mb-5 mt-10 text-lg font-medium">
                        @lang('shop::app.complaint-book.create.good-section')
                    </h2>

                    <div class="grid grid-cols-2 gap-x-5 max-sm:grid-cols-1">
                        <!-- Good Type -->
                        <x-shop::form.control-group>
                            <x-shop::form.control-group.label class="required">
                                @lang('shop::app.complaint-book.create.good-type')
                            </x-shop::form.control-group.label>

                            <x-shop::form.control-group.control
                                type="select"
                                class="px-6 py-5 max-md:py-3 max-sm:py-3.5"
                                name="good_type"
                                rules="required"
                                :value="old('good_type')"
                                :label="trans('shop::app.complaint-book.create.good-type')"
                            >
                                <option value="producto">
                                    @lang('shop::app.complaint-book.create.good-type-product')
                                </option>

                                <option value="servicio">
                                    @lang('shop::app.complaint-book.create.good-type-service')
                                </option>
                            </x-shop::form.control-group.control>

                            <x-shop::form.control-group.error control-name="good_type" />
                        </x-shop::form.control-group>

                        <!-- Claimed Amount -->
                        <x-shop::form.control-group>
                            <x-shop::form.control-group.label>
                                @lang('shop::app.complaint-book.create.claimed-amount')
                            </x-shop::form.control-group.label>

                            <x-shop::form.control-group.control
                                type="number"
                                class="px-6 py-5 max-md:py-3 max-sm:py-3.5"
                                name="claimed_amount"
                                rules=""
                                step="0.01"
                                min="0"
                                :value="old('claimed_amount')"
                                :label="trans('shop::app.complaint-book.create.claimed-amount')"
                                :placeholder="trans('shop::app.complaint-book.create.claimed-amount')"
                            />

                            <x-shop::form.control-group.error control-name="claimed_amount" />
                        </x-shop::form.control-group>
                    </div>

                    <!-- Good Description -->
                    <x-shop::form.control-group>
                        <x-shop::form.control-group.label class="required">
                            @lang('shop::app.complaint-book.create.good-description')
                        </x-shop::form.control-group.label>

                        <x-shop::form.control-group.control
                            type="text"
                            class="px-6 py-5 max-md:py-3 max-sm:py-3.5"
                            name="good_description"
                            rules="required"
                            :value="old('good_description')"
                            :label="trans('shop::app.complaint-book.create.good-description')"
                            :placeholder="trans('shop::app.complaint-book.create.good-description')"
                        />

                        <x-shop::form.control-group.error control-name="good_description" />
                    </x-shop::form.control-group>

                    <!-- 3. Claim -->
                    <h2 class="mb-5 mt-10 text-lg font-medium">
                        @lang('shop::app.complaint-book.create.claim-section')
                    </h2>

                    <!-- Type -->
                    <x-shop::form.control-group>
                        <x-shop::form.control-group.label class="required">
                            @lang('shop::app.complaint-book.create.type')
                        </x-shop::form.control-group.label>

                        <div class="grid grid-cols-2 gap-4 max-sm:grid-cols-1">
                            <label for="type_reclamo" class="flex cursor-pointer gap-2.5 rounded-lg border border-zinc-200 p-4">
                                <x-shop::form.control-group.control
                                    type="radio"
                                    id="type_reclamo"
                                    name="type"
                                    value="reclamo"
                                    for="type_reclamo"
                                    rules="required"
                                    ::checked="true"
                                    :label="trans('shop::app.complaint-book.create.type')"
                                />

                                <span>
                                    <span class="block text-sm font-medium">
                                        @lang('shop::app.complaint-book.create.type-claim')
                                    </span>

                                    <span class="mt-1 block text-xs text-zinc-500">
                                        @lang('shop::app.complaint-book.create.type-claim-desc')
                                    </span>
                                </span>
                            </label>

                            <label for="type_queja" class="flex cursor-pointer gap-2.5 rounded-lg border border-zinc-200 p-4">
                                <x-shop::form.control-group.control
                                    type="radio"
                                    id="type_queja"
                                    name="type"
                                    value="queja"
                                    for="type_queja"
                                    rules="required"
                                    :label="trans('shop::app.complaint-book.create.type')"
                                />

                                <span>
                                    <span class="block text-sm font-medium">
                                        @lang('shop::app.complaint-book.create.type-complaint')
                                    </span>

                                    <span class="mt-1 block text-xs text-zinc-500">
                                        @lang('shop::app.complaint-book.create.type-complaint-desc')
                                    </span>
                                </span>
                            </label>
                        </div>

                        <x-shop::form.control-group.error control-name="type" />
                    </x-shop::form.control-group>

                    <!-- Detail -->
                    <x-shop::form.control-group>
                        <x-shop::form.control-group.label class="required">
                            @lang('shop::app.complaint-book.create.detail')
                        </x-shop::form.control-group.label>

                        <x-shop::form.control-group.control
                            type="textarea"
                            class="px-6 py-5 max-md:py-3 max-sm:py-3.5"
                            name="detail"
                            rules="required"
                            :value="old('detail')"
                            :label="trans('shop::app.complaint-book.create.detail')"
                            rows="5"
                        />

                        <x-shop::form.control-group.error control-name="detail" />
                    </x-shop::form.control-group>

                    <!-- Request -->
                    <x-shop::form.control-group>
                        <x-shop::form.control-group.label class="required">
                            @lang('shop::app.complaint-book.create.request')
                        </x-shop::form.control-group.label>

                        <x-shop::form.control-group.control
                            type="textarea"
                            class="px-6 py-5 max-md:py-3 max-sm:py-3.5"
                            name="request"
                            rules="required"
                            :value="old('request')"
                            :label="trans('shop::app.complaint-book.create.request')"
                            rows="3"
                        />

                        <x-shop::form.control-group.error control-name="request" />
                    </x-shop::form.control-group>

                    <!-- Accept Privacy -->
                    <x-shop::form.control-group class="mt-6">
                        <div class="flex items-start gap-2.5">
                            <x-shop::form.control-group.control
                                type="checkbox"
                                id="accept_privacy"
                                name="accept_privacy"
                                value="1"
                                for="accept_privacy"
                                rules="required"
                                :label="trans('shop::app.complaint-book.create.accept-privacy')"
                            />

                            <label for="accept_privacy" class="cursor-pointer text-sm text-zinc-600">
                                @lang('shop::app.complaint-book.create.accept-privacy', ['link' => '<a href="'.route('shop.cms.page', 'privacy-policy').'" class="underline" target="_blank">'.trans('shop::app.complaint-book.create.privacy-policy-link').'</a>'])
                            </label>
                        </div>

                        <x-shop::form.control-group.error control-name="accept_privacy" />
                    </x-shop::form.control-group>

                    <!-- Submit Button -->
                    <div class="mt-8 flex flex-wrap items-center gap-9 max-sm:justify-center max-sm:text-center">
                        <button
                            class="primary-button m-0 mx-auto block w-full max-w-[374px] rounded-2xl px-11 py-4 text-center text-base max-md:max-w-full max-md:rounded-lg max-md:py-3 max-sm:py-1.5 ltr:ml-0 rtl:mr-0"
                            type="submit"
                        >
                            @lang('shop::app.complaint-book.create.submit')
                        </button>
                    </div>
                </x-shop::form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (function () {
                var lookupUrl = "{{ route('shop.complaint_book.lookup_document') }}";
                var lookingUpText = @json(trans('shop::app.complaint-book.create.looking-up'));
                var lookupFailedText = @json(trans('shop::app.complaint-book.create.lookup-failed'));

                function currentDocType() {
                    var checked = document.querySelector('input[name="document_type"]:checked');

                    return checked ? checked.value : null;
                }

                function fillField(id, value) {
                    var el = document.getElementById(id);

                    if (! el) {
                        return;
                    }

                    el.value = value;
                    el.dispatchEvent(new Event('input', { bubbles: true }));
                    el.dispatchEvent(new Event('change', { bubbles: true }));
                }

                function showMessage(text, isError) {
                    var lookupMessage = document.getElementById('document-lookup-message');

                    if (! lookupMessage) {
                        return;
                    }

                    lookupMessage.textContent = text || '';
                    lookupMessage.classList.toggle('hidden', ! text);
                    lookupMessage.classList.toggle('text-red-600', !! isError);
                    lookupMessage.classList.toggle('text-zinc-500', ! isError);
                }

                /**
                 * Delegated on document + capture phase, so this keeps working
                 * even if Vue re-renders/replaces the #document_number node.
                 */
                document.addEventListener('blur', function (event) {
                    if (! event.target || event.target.id !== 'document_number') {
                        return;
                    }

                    var value = event.target.value.trim();

                    if (currentDocType() !== 'dni' || value.length !== 8) {
                        return;
                    }

                    showMessage(lookingUpText, false);

                    window.axios.post(lookupUrl, {
                            document_number: value,
                        })
                        .then(function (response) {
                            var data = response.data;

                            if (data.success) {
                                fillField('last_name', data.last_name);
                                fillField('first_name', data.first_name);
                                showMessage('', false);
                            } else {
                                showMessage(data.message || '', true);
                            }
                        })
                        .catch(function () {
                            showMessage(lookupFailedText, true);
                        });
                }, true);
            })();
        </script>
    @endpush
</x-shop::layouts>
