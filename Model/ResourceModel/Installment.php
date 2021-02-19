<?php
declare(strict_types=1);

namespace Leanpay\Payment\Model\ResourceModel;

use Leanpay\Payment\Api\Data\InstallmentInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Zend_Db_Select;

/**
 * Class Installment
 *
 * @package Leanpay\Payment\Model\ResourceModel
 */
class Installment extends AbstractDb
{
    /**
     * @inheridoc
     */
    protected function _construct()
    {
        $this->_init(InstallmentInterface::TABLE_NAME, InstallmentInterface::ENTITY_ID);
    }

    /**
     * @param $price
     * @param $group
     * @return string
     */
    public function getLowestInstallment($price, $group)
    {
        if (!$price) {
            return '';
        }

        $orderStatement = sprintf('%s %s', InstallmentInterface::LOAN_AMOUNT, Zend_Db_Select::SQL_ASC);
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
     * @param $price
     * @param $group
     * @return array
     */
    public function getInstallmentList($price, $group)
    {
        if (!$price) {
            return [];
        }

        $orderStatement = sprintf('%s %s', InstallmentInterface::LOAN_AMOUNT, Zend_Db_Select::SQL_ASC);
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
