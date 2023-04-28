<?php
declare(strict_types=1);

namespace Leanpay\Payment\Model\ResourceModel;

use Leanpay\Payment\Api\Data\InstallmentInterface;
use Leanpay\Payment\Helper\Data;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Installment extends AbstractDb
{
    /**
     * Resource initialization
     *
     * @inheridoc
     */
    protected function _construct()
    {
        $this->_init(InstallmentInterface::TABLE_NAME, InstallmentInterface::ENTITY_ID);
    }

    /**
     * Get lowest installment
     *
     * @param float|int $price
     * @param string $group
     * @return string
     */
    public function getLowestInstallment($price, $group, $apiType = Data::API_ENDPOINT_SLOVENIA)
    {
        if (!$price) {
            return '';
        }

        $connection = $this->getConnection();
        $table = $connection->getTableName(InstallmentInterface::TABLE_NAME);

        $orderStatement = sprintf('%s %s', InstallmentInterface::INSTALLMENT_AMOUNT, 'ASC');
        $whereStatement = sprintf('%s.%s=?', $table, InstallmentInterface::LOAN_AMOUNT);
        $whereStatementGroup = sprintf('%s.%s=?', $table, InstallmentInterface::GROUP_NAME);
        $whereStatementApiType = sprintf('%s.%s=?', $table, InstallmentInterface::API_TYPE);
        $select = $this->getConnection()
            ->select()
            ->from($table, [InstallmentInterface::INSTALLMENT_AMOUNT])
            ->where($whereStatement, intval(round($price)))
            ->where($whereStatementGroup, $group)
            ->where($whereStatementApiType, $apiType)
            ->order($orderStatement);

        return $this->getConnection()->fetchOne($select);
    }

    /**
     * Get tooltip data
     *
     * @param float $price
     * @param string $group
     * @param bool $useAmount
     * @return string
     */
    public function getToolTipData(float $price, $group, $useAmount = true)
    {
        if (!$price) {
            return '';
        }

        if ($useAmount) {
            $orderStatement = sprintf('%s %s', InstallmentInterface::INSTALLMENT_AMOUNT, 'ASC');
        } else {
            $orderStatement = sprintf('%s %s', InstallmentInterface::INSTALLMENT_PERIOD, 'ASC');
        }

        $connection = $this->getConnection();
        $table = $connection->getTableName(InstallmentInterface::TABLE_NAME);
        $whereStatement = sprintf('%s.%s=?', $table, InstallmentInterface::LOAN_AMOUNT);
        $whereStatementGroup = sprintf('%s.%s=?', $table, InstallmentInterface::GROUP_NAME);

        $select = $this->getConnection()
            ->select()
            ->from(
                $table,
                [
                    InstallmentInterface::INSTALLMENT_AMOUNT,
                    InstallmentInterface::INSTALLMENT_PERIOD
                ]
            )
            ->where($whereStatement, round($price))
            ->where($whereStatementGroup, $group)
            ->order($orderStatement);

        return $this->getConnection()->fetchRow($select);
    }

    /**
     * Get installment list
     *
     * @param float $price
     * @param string $group
     * @return array
     */
    public function getInstallmentList(float $price, $group)
    {
        if (!$price) {
            return [];
        }

        $connection = $this->getConnection();
        $table = $connection->getTableName(InstallmentInterface::TABLE_NAME);

        $orderStatement = sprintf('%s %s', InstallmentInterface::INSTALLMENT_AMOUNT, 'DESC');
        $whereStatement = sprintf('%s.%s=?', $table, InstallmentInterface::LOAN_AMOUNT);
        $whereStatementGroup = sprintf('%s.%s=?', $table, InstallmentInterface::GROUP_NAME);
        $select = $this->getConnection()
            ->select()
            ->from(
                $table,
                [
                    InstallmentInterface::INSTALLMENT_PERIOD,
                    InstallmentInterface::INSTALLMENT_AMOUNT
                ]
            )
            ->where($whereStatement, round($price))
            ->where($whereStatementGroup, $group)
            ->order($orderStatement);

        return $this->getConnection()->fetchAssoc($select);
    }


    /**
     * @return array
     */
    public function getInstallmentCurrencies(): array
    {
        $connection = $this->getConnection();
        $table = $connection->getTableName(InstallmentInterface::TABLE_NAME);

        $select = $this->getConnection()
            ->select()
            ->distinct()
            ->from(
                $table,
                [
                    InstallmentInterface::CURRENCY_CODE,
                ]
            );
        return $this->getConnection()->fetchAssoc($select);
    }
}
