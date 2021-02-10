<?php

namespace Leanpay\Payment\Api\Data;

/**
 * Interface InstallmentInterface
 *
 * @package Leanpay\Payment\Api\Data
 */
interface InstallmentInterface
{
    const TABLE_NAME = 'leanpay_installment';
    const ENTITY_ID = 'entity_id';
    const GROUP_ID = 'group_id';
    const GROUP_NAME = 'group_name';
    const CURRENCY_CODE = 'currency_code';
    const LOAN_AMOUNT = 'loan_amount';
    const INSTALLMENT_PERIOD = 'installment_period';
    const INSTALLMENT_AMOUNT = 'installment_amount';

    /**
     * @return string|null
     */
    public function getEntityId();

    /**
     * @return string|null
     */
    public function getGroupId();

    /**
     * @return string|null
     */
    public function getGroupName();

    /**
     * @return string|null
     */
    public function getCurrencyCode();

    /**
     * @return int|null
     */
    public function getLoanAmount();

    /**
     * @return int|null
     */
    public function getInstallmentPeriod();

    /**
     * @return int|null
     */
    public function getInstallmentAmount();

    /**
     * @param $id
     * @return self
     */
    public function setEntityId($id);

    /**
     * @param $groupId
     * @return self
     */
    public function setGroupId($groupId);

    /**
     * @param $groupName
     * @return self
     */
    public function setGroupName($groupName);

    /**
     * @param $currencyCode
     * @return self
     */
    public function setCurrencyCode($currencyCode);

    /**
     * @param $loanAmount
     * @return self
     */
    public function setLoanAmount($loanAmount);

    /**
     * @param $installmentPeriod
     * @return self
     */
    public function setInstallmentPeriod($installmentPeriod);

    /**
     * @param $installmentAmount
     * @return self
     */
    public function setInstallmentAmount($installmentAmount);
}
