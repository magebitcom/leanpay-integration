<?php
declare(strict_types=1);

namespace Leanpay\Payment\Model;

use Leanpay\Payment\Api\Data\InstallmentInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Installment
 *
 * @package Leanpay\Payment\Model
 */
class Installment extends AbstractModel implements InstallmentInterface, IdentityInterface
{
    /**
     * Cache key
     */
    const CACHE_TAG = 'leanpay_payment_installment';

    /**
     * @var string
     */
    protected $_cacheTag = 'leanpay_payment_installment';

    /**
     * @var string
     */
    protected $_eventPrefix = 'leanpay_payment_installment';

    /**
     * @inheridoc
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Installment::class);
    }

    /**
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return string|null
     */
    public function getGroupId()
    {
        return $this->getData(InstallmentInterface::GROUP_ID);
    }

    /**
     * @return string|null
     */
    public function getGroupName()
    {
        return $this->getData(InstallmentInterface::GROUP_NAME);
    }

    /**
     * @return string|null
     */
    public function getCurrencyCode()
    {
        return $this->getData(InstallmentInterface::CURRENCY_CODE);
    }

    /**
     * @return int|null
     */
    public function getLoanAmount()
    {
        return $this->getData(InstallmentInterface::LOAN_AMOUNT);
    }

    /**
     * @return int|null
     */
    public function getInstallmentPeriod()
    {
        return $this->getData(InstallmentInterface::INSTALLMENT_PERIOD);
    }

    /**
     * @return array|int|mixed|null
     */
    public function getInstallmentAmount()
    {
        return $this->getData(InstallmentInterface::INSTALLMENT_AMOUNT);
    }

    /**
     * @param $groupId
     * @return $this
     */
    public function setGroupId($groupId)
    {
        $this->setData(InstallmentInterface::GROUP_ID, $groupId);

        return $this;
    }

    /**
     * @param $groupName
     * @return $this
     */
    public function setGroupName($groupName)
    {
        $this->setData(InstallmentInterface::GROUP_NAME, $groupName);

        return $this;
    }

    /**
     * @param $currencyCode
     * @return $this
     */
    public function setCurrencyCode($currencyCode)
    {
        $this->setData(InstallmentInterface::CURRENCY_CODE, $currencyCode);

        return $this;
    }

    /**
     * @param $loanAmount
     * @return $this
     */
    public function setLoanAmount($loanAmount)
    {
        $this->setData(InstallmentInterface::LOAN_AMOUNT, $loanAmount);

        return $this;
    }

    /**
     * @param $installmentPeriod
     * @return $this
     */
    public function setInstallmentPeriod($installmentPeriod)
    {
        $this->setData(InstallmentInterface::INSTALLMENT_PERIOD, $installmentPeriod);

        return $this;
    }

    /**
     * @param $installmentAmount
     * @return $this
     */
    public function setInstallmentAmount($installmentAmount)
    {
        $this->setData(InstallmentInterface::INSTALLMENT_AMOUNT, $installmentAmount);

        return $this;
    }
}
