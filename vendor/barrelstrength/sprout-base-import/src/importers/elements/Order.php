<?php

namespace barrelstrength\sproutbaseimport\importers\elements;

use barrelstrength\sproutbaseimport\base\ElementImporter;
use barrelstrength\sproutbaseimport\SproutBaseImport;
use craft\commerce\base\Gateway;
use craft\commerce\elements\Order as OrderElement;
use Craft;
use craft\commerce\errors\PaymentException;
use craft\commerce\models\Address;
use craft\commerce\models\Customer;
use craft\commerce\Plugin;
use craft\commerce\records\Purchasable;
use craft\commerce\records\Transaction;
use craft\commerce\services\LineItems;
use craft\errors\ElementNotFoundException;
use Throwable;
use yii\base\Exception;

class Order extends ElementImporter
{
    /** @var string Session key for storing the cart number */
    protected $cookieCartId = 'commerce_cookie';

    /**
     * @return string
     */
    public function getName(): string
    {
        return Craft::t('sprout-base-import', 'Commerce Orders');
    }

    /**
     * @return mixed
     */
    public function getModelName(): string
    {
        return OrderElement::class;
    }

    /**
     * @param       $model
     * @param array $settings
     *
     * @return bool|mixed|void
     * @throws Throwable
     * @throws ElementNotFoundException
     * @throws Exception
     */
    public function setModel($model, array $settings = [])
    {
        /** @var Plugin $commerce */
        $commerce = Plugin::getInstance();

        $number = $settings['attributes']['number'] ?? null;

        if ($number) {
            $orderCart = $commerce->getOrders()->getOrderByNumber($number);
            if ($orderCart) {
                $this->model = $orderCart;
            }
        }

        $orderStatusId = $settings['attributes']['orderStatusId'] ?? null;

        if (!$orderStatusId) {
            $orderStatus = $commerce->getOrderStatuses()->getDefaultOrderStatus();
            $orderStatusId = $orderStatus->id;
        } elseif (is_string($orderStatusId)) {
            $orderStatus = $commerce->getOrderStatuses()->getOrderStatusByHandle($orderStatusId);

            $orderStatusId = $orderStatus->id ?? null;
            // Avoid setAttributes error
            unset($settings['attributes']['orderStatusId']);
        }

        $this->model->setAttributes($settings['attributes'], false);

        if ($this->model->id === null) {
            $this->model->number = $commerce->getCarts()->generateCartNumber();
        }

        $customer = null;
        $customerEmail = $settings['attributes']['customerId'] ?? null;

        if (is_string($customerEmail)) {
            $user = Craft::$app->users->getUserByUsernameOrEmail($customerEmail);

            if ($user) {
                $customer = $commerce->getCustomers()->getCustomerByUserId((int)$user->id);

                if ($customer) {
                    $this->model->customerId = $customer->id;
                } else {
                    $customer = new Customer();

                    if ($user) {
                        $customer->userId = $user->id;
                    }
                }

                $commerce->getCustomers()->saveCustomer($customer);
            }
        } else {
            $customer = $commerce->getCustomers()->getCustomerById($customerEmail);
        }

        if ($customer === null) {
            if ($customerEmail === null) {
                $message = Craft::t('sprout-base-import',
                    'customerId attribute is required.');
            } else {
                $message = Craft::t('sprout-base-import',
                    'The customer '.$customerEmail.' was not found.');
            }

            throw new Exception($message);
        }

        if ($customer) {
            $this->model->customerId = $customer->id;
        }

        if ($this->model !== null) {
            $this->model->orderStatusId = $orderStatusId;

            Craft::$app->getElements()->saveElement($this->model, false);
        }

        $address = $settings['addresses']['billingAddress'] ?? null;
        if ($address) {
            $billingAddress = new Address();

            $billingAddress->firstName = $address['firstName'];
            $billingAddress->lastName = $address['lastName'];
            $countryCode = $address['countryCode'];

            $countryObj = $commerce->getCountries()->getCountryByIso($countryCode);
            $billingAddress->countryId = $countryObj ? $countryObj->id : null;

            if ($billingAddress->countryId) {
                $stateObj = $commerce
                    ->getStates()
                    ->getStateByAbbreviation($billingAddress->countryId, $address['state']);

                $stateId = $stateObj ? $stateObj->id : null;

                if ($stateId) {
                    $billingAddress->stateId = $stateId;
                } else {
                    $billingAddress->stateName = $address['state'];
                }

                $billingAddress->address1 = $address['address1'] ?? null;
                $billingAddress->city = $address['city'] ?? null;
            }

            $billingAddress->zipCode = $address['zipCode'] ?? null;

            $commerce->getCustomers()->saveAddress($billingAddress, $customer);

            $customer->primaryBillingAddressId = $billingAddress->id;
            $customer->primaryShippingAddressId = $billingAddress->id;

            $commerce->getCustomers()->saveCustomer($customer);
        }

        if ($customer->id) {
            $this->model->customerId = $customer->id;
        }

        if ($this->model->customer) {
            $billingAddress = $this->model->customer->getPrimaryBillingAddress();

            $shippingAddress = $this->model->customer->getPrimaryShippingAddress();
            $this->model->setBillingAddress($billingAddress);
            $this->model->setShippingAddress($shippingAddress);
        }

        $lineObjectItems = [];
        if ($settings['lineItems']) {

            // Remove line item if it exist to avoid appending of line item values
            $commerce->getLineItems()->deleteAllLineItemsByOrderId($this->model->id);

            foreach ($settings['lineItems'] as $item) {

                $purchasableId = $item['purchasableId'] ?? null;

                $sku = $item['sku'] ?? null;

                if ($sku) {
                    /** @var \craft\commerce\base\Purchasable $purchasable */
                    $purchasable = Purchasable::find()->where(['sku' => $sku])->one();

                    if ($purchasable) {
                        $purchasableId = $purchasable->id;
                    }
                }

                /** @var LineItems $lineItems */
                $lineItems = $commerce->getLineItems();
                $lineItem = $lineItems->resolveLineItem($this->model->id, $purchasableId, $item['options']);

                $lineObjectItems[] = $lineItem;

                if ($this->model === null) {
                    $this->model->addLineItem($lineItem);
                }
            }

            if ($this->model !== null) {
                $this->model->setLineItems($lineObjectItems);
            }
        }

        $this->model->isCompleted = $settings['attributes']['isCompleted'] ?? 1;

        $this->model->paymentCurrency = $commerce->getPaymentCurrencies()->getPrimaryPaymentCurrencyIso();
    }

    /**
     * @return bool|void
     * @throws Throwable
     */
    public function save()
    {
        parent::save();

        $utilities = SproutBaseImport::$app->importUtilities;

        $settings = $this->rows;
        $order = $this->model;

        if ($settings['payments']) {
            $gateway = $order->getGateway();

            if (!$gateway) {
                $error = Craft::t('sprout-base-import', 'There is no gateway selected for this order.');
                $utilities->addError('invalid-gateway', $error);
            }
            /**
             * @var $gateway Gateway
             */
            // Get the gateway's payment form
            $paymentForm = $gateway->getPaymentFormModel();

            $paymentForm->setAttributes($settings['payments'], false);

            $redirect = '';
            $transaction = null;
            if (!$paymentForm->hasErrors() && !$order->hasErrors()) {
                try {
                    Plugin::getInstance()->getPayments()->processPayment($order, $paymentForm, $redirect, $transaction);

                    if (!empty($settings['transactions']) && $transaction) {
                        $transactionRecord = Transaction::findOne($transaction->id);

                        if ($status = $settings['transactions']['status']) {
                            $transactionRecord->status = $status;
                        }

                        if ($reference = $settings['transactions']['reference']) {
                            $transactionRecord->reference = $reference;
                        }

                        if ($response = $settings['transactions']['response']) {
                            $transactionRecord->response = $response;
                        }

                        $transactionRecord->save();
                    }
                } catch (PaymentException $exception) {
                    $utilities->addError('invalid-payment', $exception->getMessage());
                }
            } else {
                $customError = Craft::t('sprout-base-import', 'Invalid payment or order. Please review.');
                $utilities->addError('invalid-payment', $customError);
            }
        }
    }

    /**
     * @param $id
     *
     * @return bool
     * @throws Throwable
     */
    public function deleteById($id): bool
    {
        return Craft::$app->elements->deleteElementById($id);
    }

    public function getImporterDataKeys(): array
    {
        return ['lineItems', 'payments', 'transactions', 'addresses'];
    }

    /**
     * This is code is from Commerce_CartService copied it because it is private
     *
     * @return mixed|string
     */
    private function getRandomCartNumber()
    {
        $number = md5(uniqid(mt_rand(), true));

        /** @var Plugin $plugin */
        $plugin = Plugin::getInstance();
        $order = $plugin->getOrders()->getOrderByNumber($number);

        // Make sure not duplicate number
        if ($order) {
            return $this->getRandomCartNumber();
        }

        return $number;
    }

    public function getFieldLayoutId($model)
    {
        // TODO: Implement getFieldLayoutId() method.
    }
}