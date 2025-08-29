<?php

namespace Botble\Payfast\Providers;

use Botble\Base\Traits\LoadAndPublishDataTrait;
use Illuminate\Support\ServiceProvider;
use Botble\Payfast\Providers\HookServiceProvider;

class PayfastServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        if (! is_plugin_active('payment')) {
            return;
        }

        $this->setNamespace('plugins/payfast')
            ->loadHelpers()
            ->loadRoutes()
            ->loadAndPublishViews()
            ->publishAssets();

        $this->app->register(HookServiceProvider::class);

        $config = $this->app['config'];

        $config->set([
            'payfast.merchantId' => get_payment_setting('merchant_id', PAYFAST_PAYMENT_METHOD_NAME),
            'payfast.merchantKey' => get_payment_setting('merchant_key', PAYFAST_PAYMENT_METHOD_NAME),
            'payfast.paymentUrl' => 'https://www.payfast.co.za/eng/process',
        ]);
    }
}
