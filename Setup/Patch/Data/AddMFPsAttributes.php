<?php
declare (strict_types = 1);

namespace Leanpay\Payment\Setup\Patch\Data;

use Leanpay\Payment\Model\Config\Source\ExclusiveOrInclusive;
use Leanpay\Payment\Model\Config\Source\VendorAttribute;
use Magento\Catalog\Model\Attribute\Backend\Startdate;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\Backend\Datetime;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddMFPsAttributes implements DataPatchInterface {
    /**
     * ModuleDataSetupInterface
     *
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * EavSetupFactory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;
    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory          $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }
    /**
     * {@inheritdoc}
     */
    public function apply() {
        $this->createProductAttributes();
        $this->createCategoryAttributes();
    }
    /**
     * {@inheritdoc}
     */
    public static function getDependencies() {
        return [];
    }
    /**
     * {@inheritdoc}
     */
    public function getAliases() {
        return [];
    }

    protected function createProductAttributes()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $productAttributes = [
            'leanpay_product_priority' => [
                'label' => 'Priority',
                'input' => 'text',
                'type' => 'int',
            ],
            'leanpay_product_time_based' => [
                'label' => 'Time-based',
                'input' => 'boolean',
                'source'   => Boolean::class,
                'type' => 'int',
            ],
            'leanpay_product_start_date' => [
                'label' => 'Date If Time-based From',
                'input' => 'date',
                'type' => 'datetime',
                'backend' => Startdate::class,
                'default' => NULL
            ],
            'leanpay_product_end_date' => [
                'label' => 'Date If Time-based To',
                'input' => 'date',
                'backend' => Datetime::class,
                'type' => 'datetime',
                'default' => NULL
            ],
            'leanpay_product_vendor_code' => [
                'label' => 'Leanpay Product Code / vendorProductCode',
                'input' => 'select',
                'source' => VendorAttribute::class,
                'type' => 'varchar',
            ],
            'leanpay_product_exclusive_inclusive' => [
                'label' => 'Inclusive Or Exclusive',
                'input' => 'select',
                'source' => ExclusiveOrInclusive::class,
                'type' => 'varchar',
                'default' => NULL
            ],
        ];

        $generalOptions = [
            'class' => '',
            'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
            'visible' => true,
            'required' => false,
            'user_defined' => true,
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'used_in_product_listing' => true,
            'group' => __('Leanpay Promotions'),
            'unique' => false,
        ];

        foreach ($productAttributes as $code => $attrUniqueOptions){
            $options = array_merge($attrUniqueOptions, $generalOptions);
            $eavSetup->addAttribute(Product::ENTITY, $code, $options);
        }
    }

    protected function createCategoryAttributes()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $productAttributes = [
            'leanpay_category_financing_product_value' => [
                'label' => 'Financing Product Value',
                'input' => 'text',
                'type' => 'varchar',
            ],
            'leanpay_category_priority' => [
                'label' => 'Priority',
                'input' => 'text',
                'type' => 'int',
            ],
            'leanpay_category_time_based' => [
                'label' => 'Time-based',
                'input' => 'boolean',
                'source'   => Boolean::class,
                'type' => 'int',
            ],
            'leanpay_category_start_date' => [
                'label' => 'Date If Time-based From',
                'input' => 'date',
                'type' => 'datetime',
                'backend' => Startdate::class,
                'default' => NULL
            ],
            'leanpay_category_end_date' => [
                'label' => 'Date If Time-based To',
                'input' => 'date',
                'type' => 'datetime',
                'backend' => Datetime::class,
                'default' => NULL
            ],
            'leanpay_category_vendor_code' => [
                'label' => 'Leanpay Product Code / vendorProductCode',
                'input' => 'select',
                'source' => VendorAttribute::class,
                'type' => 'varchar',
            ],
            'leanpay_category_exclusive_inclusive' => [
                'label' => 'Inclusive Or Exclusive',
                'input' => 'select',
                'source' => ExclusiveOrInclusive::class,
                'type' => 'varchar',
                'default' => NULL
            ],
        ];

        $generalOptions = [
            'class' => '',
            'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
            'visible' => true,
            'required' => false,
            'user_defined' => true,
            'unique' => false,
            'visible_on_front' => true,
            'used_in_product_listing' => true,
            'group' => 'Leanpay Promotions',
        ];

        foreach ($productAttributes as $code => $attrUniqueOptions){
            $options = array_merge($attrUniqueOptions, $generalOptions);
            $eavSetup->addAttribute(Category::ENTITY, $code, $options);
        }
    }

}
