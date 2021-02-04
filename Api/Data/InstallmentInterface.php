<?php

namespace Leanpay\Payment\Api\Data;

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

    public function getEntityId();

    public function getGroupId();

    public function getGroupName();

    public function getCurrencyCode();

    public function getLoanAmount();

    public function getInstallmentPeriod();

    public function getInstallmentAmount();

    public function setEntityId($id);

    public function setGroupId($groupId);

    public function setGroupName($groupName);

    public function setCurrencyCode($currencyCode);

    public function setLoanAmount($loanAmount);

    public function setInstallmentPeriod($installmentPeriod);

    public function setInstallmentAmount($installmentAmount);

}
