<?php
declare(strict_types=1);

namespace Leanpay\Payment\Plugin\Block\Product;

use Leanpay\Payment\Helper\InstallmentHelper;
use Leanpay\Payment\Pricing\Price\Installment;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\Product;

/**
 * Class ListProductPlugin
 *
 * @package Leanpay\Payment\Plugin\Block\Product
 */
class ListProductPlugin
{
    /**
     * @param ListProduct $subject
     * @param callable $proceed
     * @param Product $product
     *
     * @return string
     */
    public function aroundGetProductPrice(ListProduct $subject, callable $proceed, Product $product): string
    {
        $result = $proceed($product);
        $priceRender = $this->getPriceRender($subject);

        $price = '';

        if ($priceRender) {
            $price = $priceRender->render(
                Installment::PRICE_CODE,
                $product,
                [
                    'view_key' => InstallmentHelper::LEANPAY_INSTALLMENT_VIEW_OPTION_CATEGORY_PAGE
                ]
            );
        }


        return join('', [$result, $price]);
    }

    /**
     * @param $subject
     * @return mixed
     */
    protected function getPriceRender($subject)
    {
        return $subject->getLayout()
            ->getBlock('product.price.render.default')
            ->setData('is_product_list', true);
    }
}
