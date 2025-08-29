<?php

namespace Botble\Payfast\Forms;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\TextField;
use Botble\Payment\Concerns\Forms\HasAvailableCountriesField;
use Botble\Payment\Forms\PaymentMethodForm;

class PayfastPaymentMethodForm extends PaymentMethodForm
{
    use HasAvailableCountriesField;

    public function setup(): void
    {
        parent::setup();

        $this
            ->paymentId(PAYFAST_PAYMENT_METHOD_NAME)
            ->paymentName('Payfast')
            ->paymentDescription(__('Customer can buy product and pay directly using Payfast.'))
            ->paymentLogo(url('vendor/core/plugins/payfast/images/payfast.png'))
            ->paymentFeeField(PAYFAST_PAYMENT_METHOD_NAME)
            ->paymentUrl('https://payfast.co.za')
            ->paymentInstructions(view('plugins/payfast::instructions')->render())
            ->add(
                sprintf('payment_%s_merchant_id', PAYFAST_PAYMENT_METHOD_NAME),
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Merchant ID'))
                    ->value(BaseHelper::hasDemoModeEnabled() ? '*******************************' : get_payment_setting('merchant_id', PAYFAST_PAYMENT_METHOD_NAME))
            )
            ->add(
                sprintf('payment_%s_merchant_key', PAYFAST_PAYMENT_METHOD_NAME),
                'password',
                TextFieldOption::make()
                    ->label(__('Merchant Key'))
                    ->value(BaseHelper::hasDemoModeEnabled() ? '*******************************' : get_payment_setting('merchant_key', PAYFAST_PAYMENT_METHOD_NAME))
            )
            ->addAvailableCountriesField(PAYFAST_PAYMENT_METHOD_NAME);
    }
}
