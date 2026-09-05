@if (
    request()->routeIs('shop.checkout.onepage.index')
    && (bool) core()->getConfigData('sales.payment_methods.culqi.active')
)
    @php
        $publicKey = core()->getConfigData('sales.payment_methods.culqi.public_key');

        $currencyCode = core()->getCurrentCurrencyCode();

        $storeName = core()->getCurrentChannel()->name;
    @endphp

    @pushOnce('scripts')
        <script src="https://checkout.culqi.com/js/v4"></script>

        <script
            type="text/x-template"
            id="v-culqi-checkout-template"
        >
            <x-shop::button
                type="button"
                class="primary-button w-max rounded-2xl bg-navyBlue px-11 py-3 max-md:mb-4 max-md:w-full max-md:max-w-full max-md:rounded-lg max-sm:py-1.5"
                :title="trans('shop::app.checkout.onepage.summary.place-order')"
                ::disabled="isProcessing"
                ::loading="isProcessing"
                @click="pay"
            />
        </script>

        <script type="module">
            app.component('v-culqi-checkout', {
                template: '#v-culqi-checkout-template',

                data() {
                    return {
                        isProcessing: false,
                    };
                },

                methods: {
                    pay() {
                        if (typeof Culqi == 'undefined') {
                            this.$emitter.emit('add-flash', { type: 'error', message: '@lang('culqi::app.errors.invalid-configs')' });

                            return;
                        }

                        this.isProcessing = true;

                        this.$axios.get("{{ route('shop.checkout.onepage.summary') }}")
                            .then(response => {
                                const amount = Math.round(parseFloat(response.data.data.grand_total) * 100);

                                this.openCulqi(amount);
                            })
                            .catch(error => {
                                this.isProcessing = false;

                                this.$emitter.emit('add-flash', { type: 'error', message: '@lang('culqi::app.errors.something-went-wrong')' });
                            });
                    },

                    openCulqi(amount) {
                        Culqi.publicKey = "{{ $publicKey }}";

                        Culqi.settings({
                            title: "{{ $storeName }}",
                            currency: "{{ $currencyCode }}",
                            amount: amount,
                        });

                        Culqi.options({
                            lang: 'auto',
                            installments: false,
                        });

                        window.culqi = () => {
                            // Guards against a duplicate charge if Culqi's own button fires
                            // this callback more than once (e.g. a double click inside the modal).
                            if (this.isProcessing) {
                                return;
                            }

                            if (Culqi.token) {
                                this.charge(Culqi.token.id);
                            } else {
                                this.isProcessing = false;

                                if (Culqi.error) {
                                    this.$emitter.emit('add-flash', { type: 'error', message: Culqi.error.user_message || '@lang('culqi::app.errors.something-went-wrong')' });
                                }
                            }
                        };

                        Culqi.open();

                        this.isProcessing = false;
                    },

                    charge(tokenId) {
                        this.isProcessing = true;

                        this.$axios.post("{{ route('culqi.charge') }}", {
                                token_id: tokenId,
                            })
                            .then(response => {
                                window.location.href = "{{ route('shop.checkout.onepage.success') }}";
                            })
                            .catch(error => {
                                this.isProcessing = false;

                                this.$emitter.emit('add-flash', { type: 'error', message: error.response?.data?.message || '@lang('culqi::app.errors.something-went-wrong')' });
                            });
                    },
                },
            });
        </script>
    @endPushOnce
@endif
