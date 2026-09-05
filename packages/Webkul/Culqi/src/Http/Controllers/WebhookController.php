<?php

namespace Webkul\Culqi\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Receive a Culqi webhook call.
     *
     * We don't yet know the exact payload shape Culqi sends for each
     * resource/action/result combination, so for now this only records
     * the raw request. Once we've captured a real event, this is where
     * we'll reconcile it against the matching order (mark it paid,
     * create it if it's missing, flag a refund, etc).
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

        Log::channel('culqi')->info('Culqi webhook received', [
            'headers' => collect(request()->headers->all())
                ->except(['cookie', 'authorization', 'php-auth-user', 'php-auth-pw'])
                ->toArray(),
            'payload' => request()->all(),
            'raw'     => request()->getContent(),
        ]);

        return response()->json(['received' => true]);
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
