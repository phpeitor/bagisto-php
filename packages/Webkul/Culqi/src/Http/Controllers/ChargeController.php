<?php

namespace Webkul\Culqi\Http\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\JsonResponse;
use Webkul\Checkout\Facades\Cart;
use Webkul\Sales\Repositories\InvoiceRepository;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Transformers\OrderResource;

class ChargeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected OrderRepository $orderRepository,
        protected InvoiceRepository $invoiceRepository
    ) {}

    /**
     * Charge the Culqi token and, on success, place the order.
     *
     * @return JsonResponse
     */
    public function store()
    {
        $cart = Cart::getCart();

        // Guest carts aren't re-fetched by active status, so a cart already
        // converted into an order (e.g. a duplicate token from a double click)
        // would otherwise be charged and turned into a second order here.
        if (
            ! $cart
            || ! $cart->is_active
        ) {
            return response()->json(['message' => trans('culqi::app.errors.something-went-wrong')], 400);
        }

        try {
            $this->validateOrder($cart);

            $this->createCharge((string) request()->input('token_id'), $cart);

            return $this->saveOrder($cart);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Create a charge on Culqi using the tokenized card.
     *
     * @param  string  $tokenId
     * @param  \Webkul\Checkout\Contracts\Cart  $cart
     * @return array
     */
    protected function createCharge($tokenId, $cart)
    {
        if (! $tokenId) {
            throw new \Exception(trans('culqi::app.errors.something-went-wrong'));
        }

        $secretKey = core()->getConfigData('sales.payment_methods.culqi.secret_key');

        $client = new Client;

        try {
            $response = $client->post('https://api.culqi.com/v2/charges', [
                'headers' => [
                    'Authorization' => 'Bearer '.$secretKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'amount'        => (int) round($cart->grand_total * 100),
                    'currency_code' => $cart->cart_currency_code,
                    'email'         => $cart->billing_address->email,
                    'source_id'     => $tokenId,
                    'description'   => 'Order for cart #'.$cart->id,
                ],
            ]);
        } catch (RequestException $e) {
            $body = $e->getResponse()
                ? json_decode($e->getResponse()->getBody()->getContents(), true)
                : null;

            throw new \Exception($body['user_message'] ?? trans('culqi::app.errors.something-went-wrong'));
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Create the order and, when applicable, its invoice.
     *
     * @param  \Webkul\Checkout\Contracts\Cart  $cart
     * @return JsonResponse
     */
    protected function saveOrder($cart)
    {
        Cart::collectTotals();

        $data = (new OrderResource($cart))->jsonSerialize();

        $order = $this->orderRepository->create($data);

        $this->orderRepository->update(['status' => 'processing'], $order->id);

        if ($order->canInvoice()) {
            $this->invoiceRepository->create($this->prepareInvoiceData($order));
        }

        Cart::deActivateCart();

        session()->flash('order_id', $order->id);

        return response()->json(['success' => true]);
    }

    /**
     * Prepare invoice data for the order.
     *
     * @param  \Webkul\Sales\Models\Order  $order
     * @return array
     */
    protected function prepareInvoiceData($order)
    {
        $invoiceData = ['order_id' => $order->id];

        foreach ($order->items as $item) {
            $invoiceData['invoice']['items'][$item->id] = $item->qty_to_invoice;
        }

        return $invoiceData;
    }

    /**
     * Validate the cart before charging and creating the order.
     *
     * @param  \Webkul\Checkout\Contracts\Cart  $cart
     * @return void
     */
    protected function validateOrder($cart)
    {
        $minimumOrderAmount = (float) core()->getConfigData('sales.order_settings.minimum_order.minimum_order_amount') ?: 0;

        if (! Cart::haveMinimumOrderAmount()) {
            throw new \Exception(trans('shop::app.checkout.cart.minimum-order-message', ['amount' => core()->currency($minimumOrderAmount)]));
        }

        if (
            $cart->haveStockableItems()
            && ! $cart->shipping_address
        ) {
            throw new \Exception(trans('shop::app.checkout.cart.check-shipping-address'));
        }

        if (! $cart->billing_address) {
            throw new \Exception(trans('shop::app.checkout.cart.check-billing-address'));
        }

        if (
            $cart->haveStockableItems()
            && ! $cart->selected_shipping_rate
        ) {
            throw new \Exception(trans('shop::app.checkout.cart.specify-shipping-method'));
        }

        if (! $cart->payment) {
            throw new \Exception(trans('shop::app.checkout.cart.specify-payment-method'));
        }
    }
}
