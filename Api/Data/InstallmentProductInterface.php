<?php

namespace Leanpay\Payment\Api\Data;

interface InstallmentProductInterface
{
    public const TABLE_NAME = 'leanpay_products';
    public const ENTITY_ID = 'entity_id';
    public const GROUP_ID = 'group_id';
    public const GROUP_NAME = 'group_name';
    public const COUNTRY = 'country';

    public const validCountries = ['si', 'hr'];

    /**
     * Get group id
     *
     * @return string
     */
    public function getGroupId(): string;

    /**
     * Set group id
     *
     * @param string $id
     * @return $this
     */
    public function setGroupId(string $id): self;

    /**
     *  Gets Product country
     *
     * @return string
     */
    public function getCountry(): string;

    /**
     * Sets Product country
     *
     * @param string $country
     * @return $this
     */
    public function setCountry(string $country): self;

    /**
     * Validates if country is in allow list
     *
     * @param string $country
     * @return bool
     */
    public function validateCountry(string $country = 'si'): bool;

    /**
     * Get group name
     *
     * @return string
     */
    public function getGroupName(): string;

    /**
     * Set group name
     *
     * @param string $groupName
     * @return self
     */
    public function setGroupName(string $groupName);
}
