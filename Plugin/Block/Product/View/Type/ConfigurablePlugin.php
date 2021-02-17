<?php
/**
 * Magebit_$MODULE_NAME
 *
 * @category     Magebit
 * @package      Magebit_$MODULE_NAME
 * @author       Rihards Ratke
 * @copyright    Copyright (c) 2021 Magebit, Ltd.(https://www.magebit.com/)
 */

namespace Leanpay\Payment\Plugin\Block\Product\View\Type;

use Leanpay\Payment\Block\Installment\Pricing\Render\TemplatePriceBox;
use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class ConfigurablePlugin
 *
 * @package Leanpay\Payment\Plugin\Block\Product\View\Type
 */
class ConfigurablePlugin
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var TemplatePriceBox
     */
    private $template;

    /**
     * @var array
     */
    private $templateCache = [];

    /**
     * ConfigurablePlugin constructor.
     *
     * @param SerializerInterface $serializer
     * @param TemplatePriceBox $templatePriceBox
     */
    public function __construct(
        SerializerInterface $serializer,
        TemplatePriceBox $templatePriceBox
    ) {
        $this->template = $templatePriceBox;
        $this->serializer = $serializer;
    }

    /**
     * @param Configurable $subject
     * @param $result
     * @return string
     */
    public function afterGetJsonConfig(Configurable $subject, $result)
    {
        $json = $this->serializer->unserialize($result);

        if (
            isset($json['optionPrices']) &&
            $subject->getRequest()->getControllerName() === 'product'
        ) {
            $prices = $json['optionPrices'];

            foreach ($prices as $key => $price) {
                if (isset($price['finalPrice'], $price['finalPrice']['amount'])) {
                    $amount = $price['finalPrice']['amount'];
                    $prices[$key]['instalment_html'] = $this->getHtmlFromCache($amount);
                }
            }
            $json['optionPrices'] = $prices;
        }

        return $this->serializer->serialize($json);
    }

    /**
     * @param $amount
     * @return mixed|string
     */
    private function getHtmlFromCache($amount): string
    {
        if (!isset($this->templateCache[$amount])) {
            $this->templateCache[$amount] = $this->template->setData('amount', $amount)->toHtml();
        }

        return $this->templateCache[$amount];
    }
}
