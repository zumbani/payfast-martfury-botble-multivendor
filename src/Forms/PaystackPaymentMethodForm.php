<?php

namespace Botble\Paystack\Forms;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\TextField;
use Botble\Payment\Concerns\Forms\HasAvailableCountriesField;
use Botble\Payment\Forms\PaymentMethodForm;

class PaystackPaymentMethodForm extends PaymentMethodForm
{
    use HasAvailableCountriesField;

    public function setup(): void
    {
        parent::setup();

        $this
            ->paymentId(PAYSTACK_PAYMENT_METHOD_NAME)
            ->paymentName('Paystack')
            ->paymentDescription(__('Customer can buy product and pay directly using Visa, Credit card via :name', ['name' => 'Paystack']))
            ->paymentLogo(url('vendor/core/plugins/paystack/images/paystack.png'))
            ->paymentFeeField(PAYSTACK_PAYMENT_METHOD_NAME)
            ->paymentUrl('https://paystack.com')
            ->paymentInstructions(view('plugins/paystack::instructions')->render())
            ->add(
                sprintf('payment_%s_public', PAYSTACK_PAYMENT_METHOD_NAME),
                TextField::class,
                TextFieldOption::make()
                    ->label(__('Public Key'))
                    ->value(BaseHelper::hasDemoModeEnabled() ? '*******************************' : get_payment_setting('public', PAYSTACK_PAYMENT_METHOD_NAME))
            )
            ->add(
                sprintf('payment_%s_secret', PAYSTACK_PAYMENT_METHOD_NAME),
                'password',
                TextFieldOption::make()
                    ->label(__('Secret Key'))
                    ->value(BaseHelper::hasDemoModeEnabled() ? '*******************************' : get_payment_setting('secret', PAYSTACK_PAYMENT_METHOD_NAME))
            )
            ->addAvailableCountriesField(PAYSTACK_PAYMENT_METHOD_NAME);
    }
}
