<?php

namespace H4w\Datafrete\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use \H4w\Datafrete\Helper\Data as DatafreteData;

class Api extends AbstractHelper
{

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        DatafreteData $helperData
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->helperData  = $helperData;
    }

    public function callCotationWebservice($destinationPostcode, $productsList, $additionalInformation)
    {
        $storeScope  = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $restUrl     = $this->scopeConfig->getValue('carriers/datafrete/restCotationUrl', $storeScope);
        $accessToken = $this->scopeConfig->getValue('carriers/datafrete/accessToken', $storeScope);

        $collectPostcode     = $this->helperData->getOnlyNumbers($this->scopeConfig->getValue('shipping/origin/postcode', $storeScope));
        $destinationPostcode = $this->helperData->getOnlyNumbers($destinationPostcode);

        $body = [
            'token' 	 => $accessToken,
            'cepOrigem'  => $collectPostcode,
		    'cepDestino' => $destinationPostcode,
            'produtos' 	 => $productsList,
		    'infComp'	 => $additionalInformation,
        ];

		$curl = curl_init();
		curl_setopt_array($curl, [
		    CURLOPT_URL            => $restUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FRESH_CONNECT  => true,
		    CURLOPT_MAXREDIRS      => 10,
		    CURLOPT_TIMEOUT        => 30,
		    CURLOPT_CUSTOMREQUEST  => "POST",
		    CURLOPT_POSTFIELDS     => json_encode($body),
		    CURLOPT_HTTPHEADER     => [
                "Content-Type: application/json"
            ],
        ]);

		$response = curl_exec($curl);
		curl_close($curl);

        return json_decode($response, true);
    }

    public function callPostbackWebservice($pedido, $tabela, $cotacao)
    {
        $storeScope  = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $restUrl     = $this->scopeConfig->getValue('carriers/datafrete/restPostbackUrl', $storeScope);
        $accessToken = $this->scopeConfig->getValue('carriers/datafrete/accessToken', $storeScope);

        $body = [
            'token' 	 => $accessToken,
            'pedido'     => $pedido,
            'cod_tabela' => $tabela,
            'cotacao_id' => $cotacao,
        ];

		$curl = curl_init();
		curl_setopt_array($curl, [
		    CURLOPT_URL            => $restUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FRESH_CONNECT  => true,
		    CURLOPT_MAXREDIRS      => 10,
		    CURLOPT_TIMEOUT        => 30,
		    CURLOPT_CUSTOMREQUEST  => "POST",
		    CURLOPT_POSTFIELDS     => json_encode($body),
		    CURLOPT_HTTPHEADER     => [
                "Content-Type: application/json"
            ],
        ]);

		$response = curl_exec($curl);
		curl_close($curl);

        return json_decode($response, true);
    }

}