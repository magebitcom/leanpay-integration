<?php
declare(strict_types=1);

namespace Leanpay\Payment\Model;

use Leanpay\Payment\Api\Data\InstallmentProductInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * InstallmentProduct class
 */
class InstallmentProduct extends AbstractModel implements InstallmentProductInterface, IdentityInterface
{
    /**
     * Cache key
     */
    public const CACHE_TAG = 'leanpay_payment_installment_product';

    /**
     * @var string
     */
    protected $_cacheTag = 'leanpay_payment_installment_product';

    /**
     * @var string
     */
    protected $_eventPrefix = 'leanpay_payment_installment_product';

    /**
     * Resource initialization
     *
     * @inheridoc
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\InstallmentProduct::class);
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
     * @return string
     */
    public function getCountry(): string
    {
        return (string)$this->getData(self::COUNTRY);
    }

    /**
     * @return string
     */
    public function getGroupId(): string
    {
        return (string)$this->getData(self::GROUP_ID);
    }

    /**
     * @return string
     */
    public function getGroupName(): string
    {
        return (string)$this->getData(self::GROUP_NAME);
    }

    /**
     * @param string $country
     * @return InstallmentProductInterface
     */
    public function setCountry(string $country): InstallmentProductInterface
    {
        $this->setData(self::COUNTRY, $country);

        return $this;
    }

    /**
     * @param string $groupName
     * @return InstallmentProductInterface
     */
    public function setGroupName(string $groupName): InstallmentProductInterface
    {
        $this->setData(self::GROUP_NAME, $groupName);

        return $this;
    }

    /**
     * @param string $groupId
     * @return InstallmentProductInterface
     */
    public function setGroupId(string $groupId): InstallmentProductInterface
    {
        $this->setData(self::GROUP_ID, $groupId);

        return $this;
    }

    /**
     * @param string $country
     * @return bool
     */
    public function validateCountry(string $country = 'si'): bool
    {
        return true;
    }
}
