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
        <script src="https://js.culqi.com/checkout-js"></script>

        <script
            type="text/x-template"
            id="v-culqi-checkout-template"
        >
            <x-shop::button
                type="button"
                class="primary-button w-max rounded-2xl bg-navyBlue px-11 py-3 max-md:mb-4 max-md:w-full max-md:max-w-full max-md:rounded-lg max-sm:py-1.5"
                ::title="payLabel"
                ::disabled="isProcessing"
                ::loading="isProcessing"
                @click="pay"
            />
        </script>

        <script type="module">
            app.component('v-culqi-checkout', {
                template: '#v-culqi-checkout-template',

                props: {
                    grandTotal: {
                        type: String,
                        default: '',
                    },
                },

                data() {
                    return {
                        isProcessing: false,
                    };
                },

                computed: {
                    payLabel() {
                        const label = '@lang('culqi::app.checkout.pay')';

                        return this.grandTotal ? `${label} ${this.grandTotal}` : label;
                    },
                },

                methods: {
                    pay() {
                        if (typeof CulqiCheckout == 'undefined') {
                            this.$emitter.emit('add-flash', { type: 'error', message: '@lang('culqi::app.errors.invalid-configs')' });

                            return;
                        }

                        this.isProcessing = true;

                        this.$axios.get("{{ route('shop.checkout.onepage.summary') }}")
                            .then(response => {
                                const amount = Math.round(parseFloat(response.data.data.grand_total) * 100);

                                // Billetera, banca móvil, agente and Cuotéalo don't tokenize —
                                // they confirm payment asynchronously against a pre-created
                                // Culqi Order, so it has to exist before the widget opens.
                                return this.$axios.post("{{ route('culqi.order.create') }}")
                                    .then(orderResponse => {
                                        this.openCulqi(amount, orderResponse.data.order_id);
                                    });
                            })
                            .catch(error => {
                                this.isProcessing = false;

                                this.$emitter.emit('add-flash', { type: 'error', message: error.response?.data?.message || '@lang('culqi::app.errors.something-went-wrong')' });
                            });
                    },

                    openCulqi(amount, orderId) {
                        const culqiCheckout = new CulqiCheckout("{{ $publicKey }}", {
                            settings: {
                                title: "{{ $storeName }}",
                                currency: "{{ $currencyCode }}",
                                amount: amount,
                                order: orderId,
                            },

                            options: {
                                lang: 'auto',
                                installments: false,
                                modal: true,
                                paymentMethods: {
                                    tarjeta: true,
                                    yape: true,
                                    billetera: true,
                                    bancaMovil: true,
                                    agente: true,
                                    cuotealo: true,
                                },
                            },
                        });

                        culqiCheckout.culqi = () => {
                            // Guards against a duplicate charge if Culqi's own button fires
                            // this callback more than once (e.g. a double click inside the modal).
                            if (this.isProcessing) {
                                return;
                            }

                            if (culqiCheckout.token) {
                                // Tarjeta / Yape: tokenized, charged synchronously.
                                culqiCheckout.close();

                                this.charge(culqiCheckout.token.id);
                            } else if (culqiCheckout.order) {
                                // Billetera / banca móvil / agente / Cuotéalo: the customer
                                // already saw the payment instructions inside Culqi's modal.
                                // We just record the order as pending — the webhook confirms
                                // it later.
                                culqiCheckout.close();

                                this.placeOrder(culqiCheckout.order.id);
                            } else {
                                this.isProcessing = false;

                                if (culqiCheckout.error) {
                                    this.$emitter.emit('add-flash', { type: 'error', message: culqiCheckout.error.user_message || culqiCheckout.error.message || '@lang('culqi::app.errors.something-went-wrong')' });
                                }
                            }
                        };

                        culqiCheckout.open();

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

                    placeOrder(culqiOrderId) {
                        this.isProcessing = true;

                        this.$axios.post("{{ route('culqi.order.place') }}", {
                                order_id: culqiOrderId,
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
