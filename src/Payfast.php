<?php

namespace Botble\Payfast\Services;

class Payfast
{
    /**
     * Generate PayFast payment data array for checkout.
     *
     * @param array $data
     * @return array
     */
    public function getPaymentData(array $data): array
    {
        // Build base fields required by PayFast
        return [
            'merchant_id' => $data['merchant_id'] ?? '',
            'merchant_key' => $data['merchant_key'] ?? '',
            'amount' => $data['amount'] ?? 0,
            'item_name' => $data['description'] ?? '',
            'return_url' => $data['return_url'] ?? '',
            'cancel_url' => $data['cancel_url'] ?? '',
            'notify_url' => $data['notify_url'] ?? '',
        ];
    }
}
