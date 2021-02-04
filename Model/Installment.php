<?php
declare(strict_types=1);

namespace Leanpay\Payment\Model;

use Leanpay\Payment\Api\Data\InstallmentInterface;
use Leanpay\Payment\Model\ResourceModel\Installment\Collection;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * @method ResourceModel\Installment getResource()
 * @method Collection getCollection()
 */
class Installment extends AbstractModel implements InstallmentInterface, IdentityInterface
{
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

    public function getGroupId()
    {
        return $this->getData(InstallmentInterface::GROUP_ID);
    }

    public function getGroupName()
    {
        return $this->getData(InstallmentInterface::GROUP_NAME);
    }

    public function getCurrencyCode()
    {
        return $this->getData(InstallmentInterface::CURRENCY_CODE);
    }

    public function getLoanAmount()
    {
        return $this->getData(InstallmentInterface::LOAN_AMOUNT);
    }

    public function getInstallmentPeriod()
    {
        return $this->getData(InstallmentInterface::INSTALLMENT_PERIOD);
    }

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
