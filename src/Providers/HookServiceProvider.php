<?php

namespace Botble\Payfast\Providers;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\Payment\Facades\PaymentMethods;
use Botble\Payfast\Forms\PayfastPaymentMethodForm;
use Botble\Payfast\Services\Gateways\PayfastPaymentService;
use Botble\Payfast\Services\Payfast;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Throwable;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        add_filter(PAYMENT_FILTER_ADDITIONAL_PAYMENT_METHODS, [$this, 'registerPayfastMethod'], 16, 2);

        $this->app->booted(function (): void {
            add_filter(PAYMENT_FILTER_AFTER_POST_CHECKOUT, [$this, 'checkoutWithPayfast'], 16, 2);
        });

        add_filter(PAYMENT_METHODS_SETTINGS_PAGE, [$this, 'addPaymentSettings'], 97);

        add_filter(BASE_FILTER_ENUM_ARRAY, function ($values, $class) {
            if ($class == PaymentMethodEnum::class) {
                $values['PAYFAST'] = PAYFAST_PAYMENT_METHOD_NAME;
            }

            return $values;
        }, 21, 2);

        add_filter(BASE_FILTER_ENUM_LABEL, function ($value, $class) {
            if ($class == PaymentMethodEnum::class && $value == PAYFAST_PAYMENT_METHOD_NAME) {
                $value = 'Payfast';
            }

            return $value;
        }, 21, 2);

        add_filter(BASE_FILTER_ENUM_HTML, function ($value, $class) {
            if ($class == PaymentMethodEnum::class && $value == PAYFAST_PAYMENT_METHOD_NAME) {
                $value = Html::tag(
                    'span',
                    PaymentMethodEnum::getLabel($value),
                    ['class' => 'label-success status-label']
                )->toHtml();
            }

            return $value;
        }, 21, 2);

        add_filter(PAYMENT_FILTER_GET_SERVICE_CLASS, function ($data, $value) {
            if ($value == PAYFAST_PAYMENT_METHOD_NAME) {
                $data = PayfastPaymentService::class;
            }

            return $data;
        }, 20, 2);

        add_filter(PAYMENT_FILTER_PAYMENT_INFO_DETAIL, function ($data, $payment) {
            if ($payment->payment_channel == PAYFAST_PAYMENT_METHOD_NAME) {
                $paymentService = new PayfastPaymentService();
                $paymentDetail = $paymentService->getPaymentDetails($payment);
                if ($paymentDetail) {
                    $data .= view(
                        'plugins/payfast::detail',
                        ['payment' => $paymentDetail, 'paymentModel' => $payment]
                    )->render();
                }
            }

            return $data;
        }, 20, 2);

        add_filter(PAYMENT_FILTER_GET_REFUND_DETAIL, function ($data, $payment, $refundId) {
            if ($payment->payment_channel == PAYFAST_PAYMENT_METHOD_NAME) {
                $refundDetail = (new PayfastPaymentService())->getRefundDetails($refundId);
                if (! Arr::get($refundDetail, 'error')) {
                    $refunds = Arr::get($payment->metadata, 'refunds');
                    $refund = collect($refunds)->firstWhere('data.id', $refundId);
                    $refund = array_merge($refund, Arr::get($refundDetail, 'data', []));

                    return array_merge($refundDetail, [
                        'view' => view(
                            'plugins/payfast::refund-detail',
                            ['refund' => $refund, 'paymentModel' => $payment]
                        )->render(),
                    ]);
                }

                return $refundDetail;
            }

            return $data;
        }, 20, 3);
    }

    public function addPaymentSettings(?string $settings): string
    {
        return $settings . PayfastPaymentMethodForm::create()->renderForm();
    }

    public function registerPayfastMethod(?string $html, array $data): string
    {
        PaymentMethods::method(PAYFAST_PAYMENT_METHOD_NAME, [
            'html' => view('plugins/payfast::methods', $data)->render(),
        ]);

        return $html;
    }

    public function checkoutWithPayfast(array $data, Request $request): array
    {
        if ($data['type'] !== PAYFAST_PAYMENT_METHOD_NAME) {
            return $data;
        }

        $supportedCurrencies = (new PayfastPaymentService())->supportedCurrencyCodes();

        $paymentData = apply_filters(PAYMENT_FILTER_PAYMENT_DATA, [], $request);

        if (! in_array($paymentData['currency'], $supportedCurrencies)) {
            $data['error'] = true;
            $data['message'] = __(
                ":name doesn't support :currency. List of currencies supported by :name: :currencies.",
                [
                    'name' => 'Payfast',
                    'currency' => $paymentData['currency'],
                    'currencies' => implode(', ', $supportedCurrencies),
                ]
            );

            return $data;
        }

        try {
            $payfast = $this->app->make(Payfast::class);

            $requestData = [
                'reference' => $payfast->genTranxRef(),
                'quantity' => 1,
                'currency' => $paymentData['currency'],
                'amount' => (int) $paymentData['amount'],
                'email' => $paymentData['address']['email'],
                'callback_url' => route('payfast.payment.callback'),
                'metadata' => json_encode([
                    'order_id' => $paymentData['order_id'],
                    'customer_id' => $paymentData['customer_id'],
                    'customer_type' => $paymentData['customer_type'],
                ]),
            ];

            do_action('payment_before_making_api_request', PAYFAST_PAYMENT_METHOD_NAME, $requestData);

            $response = $payfast->getAuthorizationResponse($requestData);

            do_action('payment_after_api_response', PAYFAST_PAYMENT_METHOD_NAME, $requestData, (array) $response);

            if ($response['status'] ?? false) {
                header('Location: ' . $response['data']['authorization_url']);
                exit;
            }

            $data['error'] = true;
            $data['message'] = __('Payment failed!');
        } catch (Throwable $exception) {
            $data['error'] = true;
            $data['message'] = json_encode($exception->getMessage());

            BaseHelper::logError($exception);
        }

        return $data;
    }
}
