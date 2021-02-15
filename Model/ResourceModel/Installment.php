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
     * @return string
     */
    public function getLowestInstallment($price)
    {
        if (!$price) {
            return '';
        }

        $orderStatement = sprintf('%s %s', InstallmentInterface::LOAN_AMOUNT, Zend_Db_Select::SQL_ASC);
        $whereStatement = sprintf('%s.%s=?', InstallmentInterface::TABLE_NAME, InstallmentInterface::LOAN_AMOUNT);
        $select = $this->getConnection()
            ->select()
            ->from(InstallmentInterface::TABLE_NAME, [InstallmentInterface::INSTALLMENT_AMOUNT])
            ->columns([InstallmentInterface::INSTALLMENT_AMOUNT])
            ->where($whereStatement, round($price))
            ->order($orderStatement);

        return $this->getConnection()->fetchOne($select);
    }

    public function getInstallmentList($price): array
    {
        if (!$price) {
            return '';
        }

        $orderStatement = sprintf('%s %s', InstallmentInterface::LOAN_AMOUNT, Zend_Db_Select::SQL_ASC);
        $whereStatement = sprintf('%s.%s=?', InstallmentInterface::TABLE_NAME, InstallmentInterface::LOAN_AMOUNT);
        $select = $this->getConnection()
            ->select()
            ->from(InstallmentInterface::TABLE_NAME, [InstallmentInterface::INSTALLMENT_AMOUNT])
            ->columns(
                [
                    InstallmentInterface::INSTALLMENT_PERIOD,
                    InstallmentInterface::INSTALLMENT_AMOUNT
                ]
            )
            ->where($whereStatement, round($price))
            ->order($orderStatement);

        return $this->getConnection()->fetchAssoc($select);
    }
}
