<?php

namespace Webkul\Culqi\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Repositories\InvoiceRepository;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Repositories\OrderTransactionRepository;

class WebhookController extends Controller
{
    /**
     * States we've seen documented for an order (naming isn't confirmed
     * against a real payload yet — CulqiPanel > Desarrollo > Webhooks >
     * Historial has the raw deliveries once a real order webhook lands,
     * cross-check against those and adjust these buckets if needed).
     *
     * @var array
     */
    protected $paidStates = ['paid', 'confirmed', 'completed'];

    /**
     * @var array
     */
    protected $failedStates = ['expired', 'declined', 'failed', 'canceled', 'cancelled'];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected OrderRepository $orderRepository,
        protected InvoiceRepository $invoiceRepository,
        protected OrderTransactionRepository $orderTransactionRepository
    ) {}

    /**
     * Receive a Culqi webhook call.
     *
     * @return JsonResponse
     */
    public function store()
    {
        if (! $this->isAuthenticated()) {
            Log::channel('culqi')->warning('Culqi webhook rejected: invalid credentials', [
                'ip' => request()->ip(),
            ]);

            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $payload = request()->all();

        Log::channel('culqi')->info('Culqi webhook received', [
            'headers' => collect(request()->headers->all())
                ->except(['cookie', 'authorization', 'php-auth-user', 'php-auth-pw'])
                ->toArray(),
            'payload' => $payload,
            'raw'     => request()->getContent(),
        ]);

        if (($payload['type'] ?? null) === 'order.status.changed') {
            $this->reconcileOrder($payload);
        }

        return response()->json(['received' => true]);
    }

    /**
     * Reconcile a Culqi Order against the pending Bagisto order it was
     * placed for (see OrderController::place), marking it paid/invoiced
     * or canceled depending on the order's new state.
     *
     * @param  array  $payload
     * @return void
     */
    protected function reconcileOrder(array $payload)
    {
        $data = $payload['data'] ?? null;

        // Culqi sends `data` as a JSON-encoded string, not a nested object
        // (confirmed from a real charge.creation.succeeded delivery).
        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        $culqiOrderId = $data['id'] ?? null;
        $state = $data['state'] ?? $data['status'] ?? null;

        if (! $culqiOrderId) {
            Log::channel('culqi')->warning('Culqi order webhook missing an order id', ['payload' => $payload]);

            return;
        }

        $transaction = $this->orderTransactionRepository->findOneWhere([
            'transaction_id' => $culqiOrderId,
            'payment_method' => 'culqi',
        ]);

        if (! $transaction) {
            Log::channel('culqi')->warning('Culqi order webhook: no matching order transaction', [
                'culqi_order_id' => $culqiOrderId,
            ]);

            return;
        }

        $this->orderTransactionRepository->update([
            'status' => $state,
            'data'   => json_encode($data),
        ], $transaction->id);

        $order = $this->orderRepository->find($transaction->order_id);

        if (! $order) {
            return;
        }

        if (in_array($state, $this->paidStates)) {
            $this->orderRepository->update(['status' => Order::STATUS_PROCESSING], $order->id);

            if ($order->canInvoice()) {
                $this->invoiceRepository->create($this->prepareInvoiceData($order));
            }
        } elseif (in_array($state, $this->failedStates)) {
            $this->orderRepository->update(['status' => Order::STATUS_CANCELED], $order->id);
        } else {
            Log::channel('culqi')->warning('Culqi order webhook: unrecognized order state, order left pending', [
                'culqi_order_id' => $culqiOrderId,
                'state'          => $state,
            ]);
        }
    }

    /**
     * Verify the HTTP Basic Auth credentials Culqi sends with the webhook,
     * as configured in CulqiPanel > Desarrollo > Webhooks > Activar autenticación.
     *
     * @return bool
     */
    protected function isAuthenticated(): bool
    {
        $expectedUser = config('services.culqi.webhook_user');

        $expectedPassword = config('services.culqi.webhook_password');

        if (
            ! $expectedUser
            || ! $expectedPassword
        ) {
            return false;
        }

        return hash_equals($expectedUser, (string) request()->getUser())
            && hash_equals($expectedPassword, (string) request()->getPassword());
    }
}
