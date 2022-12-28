<?php
declare(strict_types=1);

namespace Leanpay\Payment\Model;

use Leanpay\Payment\Api\Data\InstallmentInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class Installment extends AbstractModel implements InstallmentInterface, IdentityInterface
{
    /**
     * Cache key
     */
    public const CACHE_TAG = 'leanpay_payment_installment';

    /**
     * @var string
     */
    protected $_cacheTag = 'leanpay_payment_installment';

    /**
     * @var string
     */
    protected $_eventPrefix = 'leanpay_payment_installment';

    /**
     * Resource initialization
     *
     * @inheridoc
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Installment::class);
    }

    /**
     * Get installment identities
     *
     * @return string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get group id
     *
     * @return string|null
     */
    public function getGroupId()
    {
        return $this->getData(InstallmentInterface::GROUP_ID);
    }

    /**
     * Get group name
     *
     * @return string|null
     */
    public function getGroupName()
    {
        return $this->getData(InstallmentInterface::GROUP_NAME);
    }

    /**
     * Get currency code
     *
     * @return string|null
     */
    public function getCurrencyCode()
    {
        return $this->getData(InstallmentInterface::CURRENCY_CODE);
    }

    /**
     * Get API type
     *
     * @return string|null
     */
    public function getApiType()
    {
        return $this->getData(InstallmentInterface::API_TYPE);
    }


    /**
     * Get loan amount
     *
     * @return int|null
     */
    public function getLoanAmount()
    {
        return $this->getData(InstallmentInterface::LOAN_AMOUNT);
    }

    /**
     * Get installment period
     *
     * @return int|null
     */
    public function getInstallmentPeriod()
    {
        return $this->getData(InstallmentInterface::INSTALLMENT_PERIOD);
    }

    /**
     * Get installment amount
     *
     * @return float|null
     */
    public function getInstallmentAmount()
    {
        return $this->getData(InstallmentInterface::INSTALLMENT_AMOUNT);
    }

    /**
     * Set group id
     *
     * @param string $groupId
     * @return $this
     */
    public function setGroupId($groupId)
    {
        $this->setData(InstallmentInterface::GROUP_ID, $groupId);

        return $this;
    }

    /**
     * Set group name
     *
     * @param string $groupName
     * @return $this
     */
    public function setGroupName($groupName)
    {
        $this->setData(InstallmentInterface::GROUP_NAME, $groupName);

        return $this;
    }

    /**
     * Set currency code
     *
     * @param string $currencyCode
     * @return $this
     */
    public function setCurrencyCode($currencyCode)
    {
        $this->setData(InstallmentInterface::CURRENCY_CODE, $currencyCode);

        return $this;
    }

    /**
     * Set API type
     *
     * @param string $apiType
     * @return $this
     */
    public function setApiType($apiType)
    {
        $this->setData(InstallmentInterface::API_TYPE, $apiType);

        return $this;
    }

    /**
     * Set loan amount
     *
     * @param int $loanAmount
     * @return $this
     */
    public function setLoanAmount($loanAmount)
    {
        $this->setData(InstallmentInterface::LOAN_AMOUNT, $loanAmount);

        return $this;
    }

    /**
     * Set installment period
     *
     * @param int $installmentPeriod
     * @return $this
     */
    public function setInstallmentPeriod($installmentPeriod)
    {
        $this->setData(InstallmentInterface::INSTALLMENT_PERIOD, $installmentPeriod);

        return $this;
    }

    /**
     * Set installment amount
     *
     * @param float $installmentAmount
     * @return $this
     */
    public function setInstallmentAmount($installmentAmount)
    {
        $this->setData(InstallmentInterface::INSTALLMENT_AMOUNT, $installmentAmount);

        return $this;
    }
}
