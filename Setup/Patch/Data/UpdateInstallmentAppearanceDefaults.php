<?php
/**
 * @author Magebit <info@magebit.com>
 * @copyright Copyright (c) Magebit, Ltd. (https://magebit.com)
 * @license https://magebit.com/code-license
 */
declare(strict_types=1);

namespace Leanpay\Payment\Setup\Patch\Data;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateInstallmentAppearanceDefaults implements DataPatchInterface
{
    private const UPDATES = [
        [
            'path' => 'payment/leanpay_installment/color',
            'old' => '#F58466',
            'new' => '#EB5A7A',
        ],
        [
            'path' => 'payment/leanpay_installment/background_color',
            'old' => '#F58466',
            'new' => '#EB5A7A',
        ],
        [
            'path' => 'payment/leanpay_installment/font_size_product_page',
            'old' => '15',
            'new' => '14',
        ],
        [
            'path' => 'payment/leanpay_installment/font_size_catalog_page',
            'old' => '15',
            'new' => '12',
        ],
        [
            'path' => 'payment/leanpay_installment/font_size_homepage',
            'old' => '15',
            'new' => '12',
        ],
    ];

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var ResourceConnection
     */
    private $resource;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ResourceConnection $resource
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->resource = $resource;
    }

    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $connection = $this->resource->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $table = $connection->getTableName('core_config_data');

        foreach (self::UPDATES as $update) {
            $connection->update(
                $table,
                ['value' => $update['new']],
                ['path = ?' => $update['path'], 'value = ?' => $update['old']]
            );
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}

