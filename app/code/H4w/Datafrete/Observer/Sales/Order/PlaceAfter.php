<?php

namespace H4w\Datafrete\Observer\Sales\Order;

use \H4w\Datafrete\Helper\Api as DatafreteApi;

class PlaceAfter implements \Magento\Framework\Event\ObserverInterface
{

    public function __construct(DatafreteApi $helperApi)
    {
        $this->helperApi  = $helperApi;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        $order  = $observer->getEvent()->getOrder();
        $method = $order->getShippingMethod();

        // Se o pedido foi fechado com um método Datafrete
        if (strpos($method, 'datafrete_') !== false) {
            $info = explode('<h4w>', $method);

            try {
                $this->helperApi->callPostbackWebservice($order->getIncrementId(), $info[1], $info[2]);
            } catch(Exception $e) {}
        }
    }

}