<?php

namespace Botble\Payfast\Services\Abstracts;

use Botble\Payment\Services\Traits\PaymentErrorTrait;
use Botble\Support\Services\ProduceServiceInterface;
use Illuminate\Http\Request;

abstract class PayfastPaymentAbstract implements ProduceServiceInterface
{
    use PaymentErrorTrait;

    protected ?string $paymentCurrency = null;
    protected bool $supportRefundOnline = false;
    protected float $totalAmount;

    public function __construct()
    {
        $this->paymentCurrency = config('plugins.payment.currency');
        $this->totalAmount = 0;
        $this->supportRefundOnline = false;
    }

    public function getSupportedCurrencyCodes(): array
    {
        // Payfast supports ZAR; update as needed
        return ['ZAR'];
    }

    public function getPaymentDetails($payment)
    {
        // Payfast does not support querying payment details via API in this plugin
        return false;
    }

    public function refundOrder($paymentId, $amount)
    {
        // Online refund is not supported
        return false;
    }

    public function afterMakePayment(Request $request)
    {
        // Additional actions after payment can be handled in child classes
        return false;
    }

    public function getCurrency()
    {
        return $this->paymentCurrency;
    }

    public function setCurrency($currency)
    {
        $this->paymentCurrency = $currency;
        return $this;
    }

    public function getSupportRefundOnline(): bool
    {
        return $this->supportRefundOnline;
    }
}
