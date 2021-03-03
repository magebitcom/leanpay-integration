<?php
declare(strict_types=1);

namespace Leanpay\Payment\Plugin\Block\Product;

use Leanpay\Payment\Helper\InstallmentHelper;
use Leanpay\Payment\Pricing\Price\Installment;
use Magento\Catalog\Model\Product;
use Magento\CatalogWidget\Block\Product\ProductsList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\Render;

/**
 * Class ProductsListPlugin
 *
 * @package Leanpay\Payment\Plugin\Block\Product
 */
class ProductsListPlugin
{
    /**
     * @param ProductsList $subject
     * @param callable $proceed
     * @param Product $product
     * @param null $priceType
     * @param string $renderZone
     * @param array $arguments
     * @return string
     * @throws LocalizedException
     */
    public function aroundGetProductPriceHtml(
        ProductsList $subject,
        callable $proceed,
        Product $product,
        $priceType = null,
        $renderZone = Render::ZONE_ITEM_LIST,
        array $arguments = []
    ) {
        $result = $proceed($product, $priceType, $renderZone, $arguments);

        $priceRender = $subject->getLayout()->getBlock('product.price.render.default');
        if (!$priceRender) {
            $priceRender = $this->getPriceRender($subject);
        }

        $price = $priceRender->render(
            Installment::PRICE_CODE,
            $product,
            [
                'view_key' => InstallmentHelper::LEANPAY_INSTALLMENT_VIEW_OPTION_HOMEPAGE
            ]
        );

        return join('', [$result, $price]);
    }

    /**
     * @param $subject
     * @return mixed
     */
    protected function getPriceRender($subject)
    {
        return $subject->getLayout()
            ->createBlock(
                Render::class,
                'product.price.render.default'
            );
    }
}
