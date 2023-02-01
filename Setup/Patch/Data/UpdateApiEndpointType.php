<?php

namespace Leanpay\Payment\Setup\Patch\Data;

use Leanpay\Payment\Api\Data\InstallmentInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;

class UpdateApiEndpointType implements DataPatchInterface
{

    private const OLD_CONFIG_PATH = 'payment/leanpay/leanpay_currency';

    private const NEW_CONFIG_PATH =  'payment/leanpay/api_endpoint_type';

    private const VALUE_MAP = [
        'HRK' => 'CRO',
        'EUR' => 'SLO'
    ];

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var WriterInterface
     */
    private $scopeConfiguration;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * UpdateApiEndpointType constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param ResourceConnection $resource
     * @param WriterInterface $configWriter
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ResourceConnection $resource,
        WriterInterface $configWriter

    ) {
        $this->scopeConfiguration = $configWriter;
        $this->resource = $resource;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->updateCoreConfigData();
        $this->updateInstallmentTable();
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * Transforms and migrates payment/leanpay/leanpay_currency values to payment/leanpay/api_endpoint_type
     */
    protected function updateCoreConfigData() {
        $connection = $this->resource->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $tblCoreConfigData= $connection->getTableName('core_config_data');
        $select = $connection->select()
            ->from(
                ['c' =>$tblCoreConfigData],
                ['*']
            )
            ->where('c.path = :path' );
        $bind = ['path' => self::OLD_CONFIG_PATH];
        $records = $connection->fetchAll($select, $bind);
        foreach ($records as $record) {
            $this->scopeConfiguration->save(
                self::NEW_CONFIG_PATH,
                self::VALUE_MAP[$record['value']],
                $record['scope'],
                $record['scope_id']
            );
        }

    }

    /**
     * Adds api_type installment values.
     */
    protected function updateInstallmentTable() {
        $connection = $this->resource->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $tblLeanpayInstallments= $connection->getTableName('leanpay_installment');
        foreach (self::VALUE_MAP as $currency => $endpoint) {
            $connection->update(
                $tblLeanpayInstallments,
                [InstallmentInterface::API_TYPE => $endpoint],
                ['currency_code = ?' => $currency]
            );
        }
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
    public function getAliases()
    {
        return [];
    }
}
