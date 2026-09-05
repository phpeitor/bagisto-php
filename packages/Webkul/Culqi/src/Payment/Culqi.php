<?php

namespace Webkul\Culqi\Payment;

use Illuminate\Support\Facades\Storage;
use Webkul\Payment\Payment\Payment;

class Culqi extends Payment
{
    /**
     * Payment method code.
     *
     * @var string
     */
    protected $code = 'culqi';

    /**
     * Culqi has no offline redirect, the charge is created via AJAX before the order.
     *
     * @return string
     */
    public function getRedirectUrl() {}

    /**
     * Return payment method image.
     *
     * @return string|null
     */
    public function getImage()
    {
        $url = $this->getConfigData('image');

        return $url ? Storage::url($url) : null;
    }

    /**
     * Return the Culqi public key used by Checkout.js on the storefront.
     *
     * @return string
     */
    public function getPublicKey()
    {
        return $this->getConfigData('public_key');
    }
}
