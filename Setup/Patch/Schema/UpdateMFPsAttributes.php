<?php
declare (strict_types = 1);

namespace Leanpay\Payment\Setup\Patch\Schema;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


class UpdateMFPsAttributes implements SchemaPatchInterface
{
    private $eavSetupFactory;

    private $moduleDataSetup;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->moduleDataSetup = $moduleDataSetup;
    }


    public static function getDependencies()
    {
        return [
            \Leanpay\Payment\Setup\Patch\Data\AddMFPsAttributes::class
        ];
    }


    public function getAliases()
    {
        return [];
    }


    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $productAttributes = [
            'leanpay_product_priority',
            'leanpay_product_time_based',
            'leanpay_product_start_date',
            'leanpay_product_end_date',
            'leanpay_product_vendor_code',
            'leanpay_product_exclusive_inclusive'
        ];

        foreach ($productAttributes as $productAttribute){
            $eavSetup->updateAttribute(
                Product::ENTITY,
                $productAttribute,
                'is_global',
                ScopedAttributeInterface::SCOPE_STORE,
                null
            );
        }

        $categoryAttributes = [
            'leanpay_category_priority',
            'leanpay_category_time_based',
            'leanpay_category_time_based',
            'leanpay_category_start_date',
            'leanpay_category_end_date',
            'leanpay_category_vendor_code',
            'leanpay_category_exclusive_inclusive'
        ];

        foreach ($categoryAttributes as $categoryAttribute){
            $eavSetup->updateAttribute(
                Category::ENTITY,
                $categoryAttribute,
                'is_global',
                ScopedAttributeInterface::SCOPE_STORE,
                null
            );
        }

        $this->moduleDataSetup->endSetup();
    }
}
