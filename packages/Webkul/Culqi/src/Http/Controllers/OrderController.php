<?php

namespace Webkul\Culqi\Http\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\JsonResponse;
use Webkul\Checkout\Facades\Cart;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Repositories\OrderTransactionRepository;
use Webkul\Sales\Transformers\OrderResource;

class OrderController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected OrderRepository $orderRepository,
        protected OrderTransactionRepository $orderTransactionRepository
    ) {}

    /**
     * Pre-create a Culqi Order for the current cart. Required before opening
     * the checkout widget for the payment methods that don't tokenize
     * (billetera, banca móvil, agente, cuotéalo, PagoEfectivo) — they
     * confirm the payment asynchronously against this order id instead.
     *
     * @return JsonResponse
     */
    public function store()
    {
        $cart = Cart::getCart();

        if (
            ! $cart
            || ! $cart->is_active
        ) {
            return response()->json(['message' => trans('culqi::app.errors.something-went-wrong')], 400);
        }

        try {
            $this->validateOrder($cart);

            $order = $this->createCulqiOrder($cart);

            return response()->json(['order_id' => $order['id']]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Place the Bagisto order once the widget confirms a Culqi Order was
     * generated (i.e., the customer picked a non-card/Yape method and saw
     * the payment instructions). The order stays pending until the
     * `order.status.changed` webhook confirms it was actually paid.
     *
     * @return JsonResponse
     */
    public function place()
    {
        $cart = Cart::getCart();

        if (
            ! $cart
            || ! $cart->is_active
        ) {
            return response()->json(['message' => trans('culqi::app.errors.something-went-wrong')], 400);
        }

        $culqiOrderId = (string) request()->input('order_id');

        if (! $culqiOrderId) {
            return response()->json(['message' => trans('culqi::app.errors.something-went-wrong')], 400);
        }

        try {
            $this->validateOrder($cart);

            return $this->savePendingOrder($culqiOrderId, $cart);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Create the order on Culqi's side.
     *
     * @param  \Webkul\Checkout\Contracts\Cart  $cart
     * @return array
     */
    protected function createCulqiOrder($cart)
    {
        $secretKey = core()->getConfigData('sales.payment_methods.culqi.secret_key');

        $client = new Client;

        try {
            $response = $client->post('https://api.culqi.com/v2/orders', [
                'headers' => [
                    'Authorization' => 'Bearer '.$secretKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'amount'          => (int) round($cart->grand_total * 100),
                    'currency_code'   => $cart->cart_currency_code,
                    'description'     => 'Order for cart #'.$cart->id,
                    'order_number'    => 'cart-'.$cart->id.'-'.now()->timestamp,
                    'client_details'  => [
                        'first_name'   => $cart->billing_address->first_name,
                        'last_name'    => $cart->billing_address->last_name,
                        'email'        => $cart->billing_address->email,
                        'phone_number' => $cart->billing_address->phone,
                    ],
                    'expiration_date' => now()->addDay()->timestamp,
                    'confirm'         => false,
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
     * Create the Bagisto order as pending payment and record the Culqi
     * order id so the webhook can find it again once the payment resolves.
     *
     * @param  string  $culqiOrderId
     * @param  \Webkul\Checkout\Contracts\Cart  $cart
     * @return JsonResponse
     */
    protected function savePendingOrder($culqiOrderId, $cart)
    {
        Cart::collectTotals();

        $data = (new OrderResource($cart))->jsonSerialize();

        $order = $this->orderRepository->create($data);

        $this->orderRepository->update(['status' => Order::STATUS_PENDING_PAYMENT], $order->id);

        $this->orderTransactionRepository->create([
            'transaction_id' => $culqiOrderId,
            'status'         => 'pending',
            'type'           => 'order',
            'amount'         => $order->grand_total,
            'payment_method' => 'culqi',
            'order_id'       => $order->id,
            // No invoice exists yet — this transaction only turns into a
            // real invoice once the order.status.changed webhook confirms
            // payment (see WebhookController).
            'invoice_id'     => 0,
            'data'           => json_encode(['culqi_order_id' => $culqiOrderId]),
        ]);

        Cart::deActivateCart();

        session()->flash('order_id', $order->id);

        return response()->json(['success' => true]);
    }
}
