<?php

declare(strict_types=1);

namespace Leanpay\Payment\Plugin\Block\Product\View;

use Leanpay\Payment\Block\Installment\Pricing\Render\TemplatePriceBox;
use Magento\Catalog\Block\Product\View as ProductView;
use Magento\Catalog\Block\Product\View\Options as OptionsBlock;
use Magento\Framework\Serialize\SerializerInterface;

class ViewPlugin
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
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Leanpay\Payment\Block\Installment\Pricing\Render\TemplatePriceBox $templatePriceBox
     */
    public function __construct(
        SerializerInterface $serializer,
        TemplatePriceBox $templatePriceBox
    ) {
        $this->serializer = $serializer;
        $this->template = $templatePriceBox;
    }

    /**
     * Add installment html map to product view price JSON.
     *
     * @param \Magento\Catalog\Block\Product\View $subject
     * @param string $result
     * @return string
     */
    public function afterGetJsonConfig(ProductView $subject, $result)
    {
        try {
            // Only PDP: enforce full action name to avoid category/listing pages
            $request = $subject->getRequest();
            if ($request->getFullActionName() !== 'catalog_product_view') {
                return $result;
            }

            $json = $this->serializer->unserialize($result);

            if (!isset($json['prices'])) {
                return $result;
            }

            $amounts = [];
            foreach ($json['prices'] as $price) {
                if (isset($price['amount'])) {
                    $amounts[] = $price['amount'];
                }
            }

            // Collect likely totals from customizable options (single selections)
            try {
                /** @var OptionsBlock $optionsBlock */
                $optionsBlock = $subject->getLayout()->createBlock(OptionsBlock::class);
                if ($optionsBlock) {
                    $optionsBlock->setProduct($subject->getProduct());
                    $optionsJson = $optionsBlock->getJsonConfig();
                    $optionsConfig = $this->serializer->unserialize($optionsJson);

                    $baseFinal = isset($json['prices']['finalPrice']['amount'])
                        ? (float)$json['prices']['finalPrice']['amount']
                        : 0.0;

                    foreach ($optionsConfig as $opt) {
                        if (is_array($opt)) {
                            foreach ($opt as $valueConfig) {
                                if (isset($valueConfig['prices']['finalPrice']['amount'])) {
                                    $total = $baseFinal + (float)$valueConfig['prices']['finalPrice']['amount'];
                                    $amounts[] = $total;
                                }
                            }
                        } else {
                            if (isset($opt['prices']['finalPrice']['amount'])) {
                                $total = $baseFinal + (float)$opt['prices']['finalPrice']['amount'];
                                $amounts[] = $total;
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                // best-effort enrichment
            }

            $amounts = array_unique(array_map(function ($v) {
                return (int)round((float)$v);
            }, $amounts));

            $installmentMap = [];
            foreach ($amounts as $intAmount) {
                $installmentMap[$intAmount] = $this->getHtmlFromCache($intAmount);
            }

            $json['installmentHtmlMap'] = $installmentMap;

            return $this->serializer->serialize($json);
        } catch (\Throwable $e) {
            return $result;
        }
    }

    /**
     * Get installment html amount from cache
     *
     * @param float $amount
     * @return mixed|string
     */
    private function getHtmlFromCache($amount): string
    {
        $int = intval(round($amount));
        if (!isset($this->templateCache[$int])) {
            $this->templateCache[$int] = $this->template->setData('amount', $amount)->toHtml();
        }

        return $this->templateCache[$int];
    }
}
