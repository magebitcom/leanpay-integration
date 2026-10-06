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
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable;
use Magento\Framework\Serialize\SerializerInterface;

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
     * Add installment price
     *
     * @param Configurable $subject
     * @param string $result
     * @return string
     */
    public function afterGetJsonConfig(Configurable $subject, $result)
    {
        $json = $this->serializer->unserialize($result);

        if (isset($json['optionPrices']) &&
            $subject->getRequest()->getControllerName() === 'product'
        ) {
            $prices = $json['optionPrices'];
            $variants = [];

            foreach ($subject->getAllowProducts() as $variant) {
                $variants[$variant->getId()] = $variant;
            }

            // A promotion can be set on the variant itself, so render each variant's badge with it (LMM-144)
            $this->template->setData('product', $subject->getProduct());

            foreach ($prices as $key => $price) {
                if (isset($price['finalPrice'], $price['finalPrice']['amount'])) {
                    $amount = $price['finalPrice']['amount'];
                    $prices[$key]['instalment_html'] = $this->getHtmlFromCache($amount, $variants[$key] ?? null);
                }
            }

            // The template block is shared, so do not leak the product context into other renders
            $this->template->unsetData(['product', 'variant']);
            $json['optionPrices'] = $prices;
        }

        return $this->serializer->serialize($json);
    }

    /**
     * Get installment html amount from cache
     *
     * @param float $amount
     * @param ProductInterface|null $variant
     * @return mixed|string
     */
    private function getHtmlFromCache($amount, ?ProductInterface $variant = null): string
    {
        $int = intval(round($amount));
        // Variants without their own promotion render the same badge as the parent, so share it
        $key = $variant && $variant->getData('leanpay_product_vendor_code') ? $int . '-' . $variant->getId() : $int;

        if (!isset($this->templateCache[$key])) {
            $this->templateCache[$key] = $this->template
                ->setData('amount', $amount)
                ->setData('variant', $variant)
                ->toHtml();
        }

        return $this->templateCache[$key];
    }
}
