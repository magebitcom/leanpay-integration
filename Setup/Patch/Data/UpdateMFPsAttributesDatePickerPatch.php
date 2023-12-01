<?php

declare(strict_types=1);

namespace Leanpay\Payment\Setup\Patch\Data;

use Magento\Catalog\Model\Category;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Catalog\Model\Product;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes.
 */
class UpdateMFPsAttributesDatePickerPatch implements DataPatchInterface
{
    /**
     * @var EavSetup
     */
    private EavSetup $eavSetup;
    /**
     * @var ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;

    /**
     * @param EavSetup $eavSetup
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        EavSetup $eavSetup,
        ModuleDataSetupInterface $moduleDataSetup
    )
    {
        $this->eavSetup = $eavSetup;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * Do Upgrade.
     *
     * @return void
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $this->eavSetup->updateAttribute(
            Product::ENTITY,
            'leanpay_product_end_date',
            'backend_model',
            'Leanpay\Payment\Model\Attribute\Backend\ModifyEndDate',
            null
        );

        $this->eavSetup->updateAttribute(
            Product::ENTITY,
            'leanpay_product_end_date',
            'frontend_label',
            'Date If Time-based To (same day included)',
            null
        );

        $this->eavSetup->updateAttribute(
            Category::ENTITY,
            'leanpay_category_end_date',
            'backend_model',
            'Leanpay\Payment\Model\Attribute\Backend\ModifyEndDate',
            null
        );

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * Get aliases (previous names) for the patch.
     *
     * @return string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Get array of patches that have to be executed prior to this.
     *
     * @return string[]
     */
    public static function getDependencies()
    {
        return [];
    }
}
