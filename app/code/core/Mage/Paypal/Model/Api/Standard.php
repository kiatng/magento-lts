<?php

/**
 * @copyright  For copyright and license information, read the COPYING.txt file.
 * @link       /COPYING.txt
 * @license    Open Software License (OSL 3.0)
 * @package    Mage_Paypal
 */

/**
 * PayPal Standard checkout request API
 *
 * @package    Mage_Paypal
 */
class Mage_Paypal_Model_Api_Standard extends Mage_Paypal_Model_Api_Abstract
{
    /**
     * Global interface map and export filters
     * @var array
     */
    protected $_globalMap = [
        // commands
        'business'      => 'business_account',
        'notify_url'    => 'notify_url',
        'return'        => 'return_url',
        'cancel_return' => 'cancel_url',
        'bn'            => 'build_notation_code',
        'paymentaction' => 'payment_action',
        // payment
        'invoice'       => 'order_id',
        'currency_code' => 'currency_code',
        'amount'        => 'amount',
        'shipping'      => 'shipping_amount',
        'tax'           => 'tax_amount',
        'discount_amount' => 'discount_amount',
        // misc
        'item_name'        => 'cart_summary',
        // page design settings
        'page_style'             => 'page_style',
        'cpp_header_image'       => 'hdrimg',
        'cpp_headerback_color'   => 'hdrbackcolor',
        'cpp_headerborder_color' => 'hdrbordercolor',
        'cpp_payflow_color'      => 'payflowcolor',
        'lc'                     => 'locale',
    ];
    protected $_exportToRequestFilters = [
        'amount'   => '_filterAmount',
        'shipping' => '_filterAmount',
        'tax'      => '_filterAmount',
        'discount_amount' => '_filterAmount',
    ];

    /**
     * Interface for common and "aggregated order" specific fields
     * @var array
     */
    protected $_commonRequestFields = [
        'business', 'invoice', 'currency_code', 'paymentaction', 'return', 'cancel_return', 'notify_url', 'bn',
        'page_style', 'cpp_header_image', 'cpp_headerback_color', 'cpp_headerborder_color', 'cpp_payflow_color',
        'amount', 'shipping', 'tax', 'discount_amount', 'item_name', 'lc',
    ];

    /**
      * Fields that should be replaced in debug with '***'
      *
      * @var array
      */
    protected $_debugReplacePrivateDataKeys = ['business'];

    /**
     * Line items export mapping settings
     * @var array
     */
    protected $_lineItemTotalExportMap = [
        Mage_Paypal_Model_Cart::TOTAL_SUBTOTAL => 'amount',
        Mage_Paypal_Model_Cart::TOTAL_DISCOUNT => 'discount_amount',
        Mage_Paypal_Model_Cart::TOTAL_TAX      => 'tax',
        Mage_Paypal_Model_Cart::TOTAL_SHIPPING => 'shipping',
    ];
    protected $_lineItemExportItemsFormat = [
        'id'     => 'item_number_%d',
        'name'   => 'item_name_%d',
        'qty'    => 'quantity_%d',
        'amount' => 'amount_%d',
    ];

    protected $_lineItemExportItemsFilters = [
        'qty'      => '_filterQty',
    ];

    /**
     * Address export to request map
     * @var array
     */
    protected $_addressMap = [
        'city'       => 'city',
        'country'    => 'country_id',
        'email'      => 'email',
        'first_name' => 'firstname',
        'last_name'  => 'lastname',
        'zip'        => 'postcode',
        'state'      => 'region',
        'address1'   => 'street',
        'address2'   => 'street2',
    ];

    /**
     * Generate PayPal Standard checkout request fields
     * Depending on whether there are cart line items set, will aggregate everything or display items specifically
     * Shipping amount in cart line items is implemented as a separate "fake" line item
     */
    public function getStandardCheckoutRequest()
    {
        $request = $this->_exportToRequest($this->_commonRequestFields);
        $request['charset'] = 'utf-8';

        $isLineItems = $this->_exportLineItems($request);
        if ($isLineItems) {
            $request = array_merge($request, [
                'cmd'    => '_cart',
                'upload' => 1,
            ]);
            if (isset($request['tax'])) {
                $request['tax_cart'] = $request['tax'];
            }
            if (isset($request['discount_amount'])) {
                $request['discount_amount_cart'] = $request['discount_amount'];
            }
        } else {
            $request = array_merge($request, [
                'cmd'           => '_ext-enter',
                'redirect_cmd'  => '_xclick',
            ]);
        }

        // payer address
        $this->_importAddress($request);

        return $request;
    }

    /**
     * Merchant account email getter
     * @return string
     */
    public function getBusinessAccount()
    {
        return $this->_getDataOrConfig('business_account');
    }

    /**
     * Payment action getter
     * @return string
     */
    public function getPaymentAction()
    {
        return strtolower(parent::getPaymentAction());
    }

    /**
     * @deprecated after 1.4.1.0
     *
     * @param array $request
     */
    public function debugRequest($request) {}

    /**
     * Add shipping total as a line item.
     * For some reason PayPal ignores shipping total variables exactly when line items is enabled
     * Note that $i = 1
     *
     * @param int $i
     * @return bool
     */
    protected function _exportLineItems(array &$request, $i = 1)
    {
        if (!$this->_cart) {
            return false;
        }
        if ($this->getIsLineItemsEnabled()) {
            $this->_cart->isShippingAsItem(true);
        }
        return parent::_exportLineItems($request, $i);
    }

    /**
     * Import address object, if set, to the request
     *
     * @param array|Varien_Object $request
     */
    protected function _importAddress(&$request)
    {
        $address = $this->getAddress();
        if (!$address) {
            if ($this->getNoShipping()) {
                $request['no_shipping'] = 1;
            }
            return;
        }

        $request = Varien_Object_Mapper::accumulateByMap($address, $request, array_flip($this->_addressMap));

        // Address may come without email info (user is not always required to enter it), so add email from order
        if (!$request['email']) {
            $order = $this->getOrder();
            if ($order) {
                $request['email'] = $order->getCustomerEmail();
            }
        }

        $regionCode = $this->_lookupRegionCodeFromAddress($address);
        if ($regionCode) {
            $request['state'] = $regionCode;
        }
        $this->_importStreetFromAddress($address, $request, 'address1', 'address2');
        $this->_applyCountryWorkarounds($request);

        $request['address_override'] = 1;
    }

    /**
     * Adopt specified request array to be compatible with Paypal
     * Puerto Rico should be as state of USA and not as a country
     *
     * @param array $request
     */
    protected function _applyCountryWorkarounds(&$request)
    {
        if (isset($request['country']) && $request['country'] == 'PR') {
            $request['country'] = 'US';
            $request['state']   = 'PR';
        }
    }
}
