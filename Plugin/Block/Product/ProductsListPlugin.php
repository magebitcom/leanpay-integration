<?php
declare(strict_types=1);

namespace Leanpay\Payment\Plugin\Block\Product;

use Leanpay\Payment\Helper\Data;
use Leanpay\Payment\Helper\InstallmentHelper;
use Leanpay\Payment\Pricing\Price\Installment;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogWidget\Block\Product\ProductsList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\Render;

class ProductsListPlugin
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @param Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Add installment price to plugin
     *
     * @param ProductsList $subject
     * @param callable $proceed
     * @param Product $product
     * @param string $priceType
     * @param string $renderZone
     * @param array $arguments
     * @return string
     * @throws LocalizedException
     */
    public function aroundGetProductPriceHtml(
        ProductsList $subject,
        callable $proceed,
        Product $product,
        ?string $priceType = null,
        $renderZone = Render::ZONE_ITEM_LIST,
        array $arguments = []
    ) {
        $result = $proceed($product, $priceType, $renderZone, $arguments);

        $collection = $subject->getProductCollection();
        if ($collection instanceof Collection && $collection->isLoaded()) {
            $this->helper->preloadCategoryPromotions($collection->getLoadedIds());
        }

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
     * Get layout HTML
     *
     * @param ProductsList $subject
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
