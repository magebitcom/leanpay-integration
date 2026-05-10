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

class MigrateInstallmentAdvancedConfig implements DataPatchInterface
{
    private const PATH_MIGRATIONS = [
        'payment/leanpay_installment_advanced/amount_threshold' => 'payment/leanpay_installment/advanced/amount_threshold',
        'payment/leanpay_installment_advanced/default_installment_count' => 'payment/leanpay_installment/advanced/default_installment_count',
        'payment/leanpay_installment_advanced/under_threshold_text' => 'payment/leanpay_installment/advanced/under_threshold_text',
        'payment/leanpay_installment_advanced/plp_background_color' => 'payment/leanpay_installment/advanced/plp_background_color',
        'payment/leanpay_installment_advanced/pdp_text_color' => 'payment/leanpay_installment/advanced/pdp_text_color',
        'payment/leanpay_installment_advanced/quick_information' => 'payment/leanpay_installment/advanced/quick_information',
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

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $connection = $this->resource->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $table = $connection->getTableName('core_config_data');

        foreach (self::PATH_MIGRATIONS as $oldPath => $newPath) {
            $connection->update(
                $table,
                ['path' => $newPath],
                ['path = ?' => $oldPath]
            );
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
