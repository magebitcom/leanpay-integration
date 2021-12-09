<?php

namespace Leanpay\Payment\Api\Data;

interface InstallmentInterface
{
    public const TABLE_NAME = 'leanpay_installment';
    public const ENTITY_ID = 'entity_id';
    public const GROUP_ID = 'group_id';
    public const GROUP_NAME = 'group_name';
    public const CURRENCY_CODE = 'currency_code';
    public const LOAN_AMOUNT = 'loan_amount';
    public const INSTALLMENT_PERIOD = 'installment_period';
    public const INSTALLMENT_AMOUNT = 'installment_amount';

    /**
     * Get entity id
     *
     * @return string|null
     */
    public function getEntityId();

    /**
     * Get group id
     *
     * @return string|null
     */
    public function getGroupId();

    /**
     * Get group name
     *
     * @return string|null
     */
    public function getGroupName();

    /**
     * Get currency code
     *
     * @return string|null
     */
    public function getCurrencyCode();

    /**
     * Get loan amount
     *
     * @return int|null
     */
    public function getLoanAmount();

    /**
     * Get installment period
     *
     * @return int|null
     */
    public function getInstallmentPeriod();

    /**
     * Get installment amount
     *
     * @return float|null
     */
    public function getInstallmentAmount();

    /**
     * Set entity id
     *
     * @param string $id
     * @return self
     */
    public function setEntityId($id);

    /**
     * Set group id
     *
     * @param string $groupId
     * @return self
     */
    public function setGroupId($groupId);

    /**
     * Set group name
     *
     * @param string $groupName
     * @return self
     */
    public function setGroupName($groupName);

    /**
     * Set currency code
     *
     * @param string $currencyCode
     * @return self
     */
    public function setCurrencyCode($currencyCode);

    /**
     * Set loan amount
     *
     * @param int $loanAmount
     * @return self
     */
    public function setLoanAmount($loanAmount);

    /**
     * Set installment period
     *
     * @param int $installmentPeriod
     * @return self
     */
    public function setInstallmentPeriod($installmentPeriod);

    /**
     * Set installment amount
     *
     * @param float $installmentAmount
     * @return self
     */
    public function setInstallmentAmount($installmentAmount);
}
