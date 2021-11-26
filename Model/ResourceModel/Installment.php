<?php
declare(strict_types=1);

namespace Leanpay\Payment\Model\ResourceModel;

use Leanpay\Payment\Api\Data\InstallmentInterface;
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
     * @param string $price
     * @param string $group
     * @return string
     */
    public function getLowestInstallment($price, $group)
    {
        if (!$price) {
            return '';
        }

        $orderStatement = sprintf('%s %s', InstallmentInterface::INSTALLMENT_AMOUNT, 'ASC');
        $whereStatement = sprintf('%s.%s=?', InstallmentInterface::TABLE_NAME, InstallmentInterface::LOAN_AMOUNT);
        $whereStatementGroup = sprintf('%s.%s=?', InstallmentInterface::TABLE_NAME, InstallmentInterface::GROUP_NAME);

        $select = $this->getConnection()
            ->select()
            ->from(InstallmentInterface::TABLE_NAME, [InstallmentInterface::INSTALLMENT_AMOUNT])
            ->where($whereStatement, round($price))
            ->where($whereStatementGroup, $group)
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
    public function getToolTipData($price, $group, $useAmount = true)
    {
        if (!$price) {
            return '';
        }

        if ($useAmount) {
            $orderStatement = sprintf('%s %s', InstallmentInterface::INSTALLMENT_AMOUNT, 'ASC');
        } else {
            $orderStatement = sprintf('%s %s', InstallmentInterface::INSTALLMENT_PERIOD, 'ASC');
        }
        $whereStatement = sprintf('%s.%s=?', InstallmentInterface::TABLE_NAME, InstallmentInterface::LOAN_AMOUNT);
        $whereStatementGroup = sprintf('%s.%s=?', InstallmentInterface::TABLE_NAME, InstallmentInterface::GROUP_NAME);

        $select = $this->getConnection()
            ->select()
            ->from(
                InstallmentInterface::TABLE_NAME,
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
    public function getInstallmentList($price, $group)
    {
        if (!$price) {
            return [];
        }

        $orderStatement = sprintf('%s %s', InstallmentInterface::INSTALLMENT_AMOUNT, 'DESC');
        $whereStatement = sprintf('%s.%s=?', InstallmentInterface::TABLE_NAME, InstallmentInterface::LOAN_AMOUNT);
        $whereStatementGroup = sprintf('%s.%s=?', InstallmentInterface::TABLE_NAME, InstallmentInterface::GROUP_NAME);
        $select = $this->getConnection()
            ->select()
            ->from(
                InstallmentInterface::TABLE_NAME,
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
}
