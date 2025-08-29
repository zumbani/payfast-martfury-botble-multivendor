<?php

namespace Botble\Payfast;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Botble\Setting\Facades\Setting;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Setting::delete([
            'payment_payfast_name',
            'payment_payfast_description',
            'payment_payfast_merchant_id',
            'payment_payfast_merchant_key',
            'payment_payfast_status',
        ]);
    }
}
