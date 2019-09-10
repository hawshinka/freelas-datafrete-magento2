<?php

namespace H4w\Datafrete\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use \H4w\Datafrete\Helper\{Data as DatafreteData, Api as DatafreteApi};

class Datafrete extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements \Magento\Shipping\Model\Carrier\CarrierInterface
{

    protected $_code = 'datafrete'; // Keet it like this
    protected $_isFixed = false;
    protected $_rateResultFactory;
    protected $_rateMethodFactory;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        array $data = [],
        DatafreteData $helperData,
        DatafreteApi $helperApi
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);

        $this->scopeConfig = $scopeConfig;
        $this->helperData  = $helperData;
        $this->helperApi   = $helperApi;
    }

    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        $definePrazoExibicao = function($shippingMethod) use ($storeScope) {
            if (!empty($shippingMethod['prazo_exibicao'])) {
                return (int) $shippingMethod['prazo_exibicao'];
            }
            if (!empty($shippingMethod['prazo'])) {
                return (int) $shippingMethod['prazo'];
            }
            return (int) $this->scopeConfig->getValue('carriers/datafrete/defaultShippingDeadline', $storeScope);
        };

        $defineValorExibicao = function($shippingMethod) {
            return (double) $shippingMethod['valor_frete_exibicao'] ?? $shippingMethod['valor_frete'];
        };

		try {
            $productsList 		   = $this->helperData->prepareProductsList($request->getAllItems());
            $additionalInformation = $this->helperData->prepareAdditionalInformation();
            $wsResults             = $this->helperApi->callCotationWebservice($request->getDestPostcode(), $productsList, $additionalInformation);

            if ((int) $wsResults['codigo_retorno'] !== 1) {
                throw new Exception($wsResults['data'], $wsResults['codigo_retorno']);
            }

			$deadlineMessage = trim($this->scopeConfig->getValue('carriers/datafrete/msgShippingDeadline', $storeScope));
            $methodsResults  = $this->_rateResultFactory->create();

			foreach($wsResults['data'] as $shippingMethod) {
                $prazoExibicao = $definePrazoExibicao($shippingMethod);
                $valorExibicao = $defineValorExibicao($shippingMethod);

                $method = $this->_rateMethodFactory->create();
				$method->setCarrier($this->_code); // Keep it like this
                $method->setPrice($valorExibicao);
				$method->setCost($valorExibicao);

				$method->setCarrierTitle($this->scopeConfig->getValue('carriers/datafrete/title', $storeScope));
				$method->setMethodTitle($shippingMethod['descricao']);

                if (isset($deadlineMessage)) {
                    $method->setMethodTitle($method->getMethodTitle() . ' ' . sprintf($deadlineMessage, (string) $prazoExibicao));
                }

				$method->setMethod($this->helperData->buildShippingMethodName($shippingMethod['descricao']). '<h4w>' .$shippingMethod['cod_tabela']. '<h4w>' .$wsResults['CotacaoId']);
				$methodsResults->append($method);
			}

			return $methodsResults;
		} catch (Exception $e) {
			return false;
		}
    }

    public function getAllowedMethods()
    {
        return ['flatrate' => $this->getConfigData('title')];
    }

}
