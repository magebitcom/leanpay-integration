<?php

namespace Leanpay\Payment\Api;

use Leanpay\Payment\Api\Data\InstallmentInterface;
use Leanpay\Payment\Api\Data\InstallmentProductInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface InstallmentProductRepositoryInterface
{
    /**
     * Get VendorProduct by id
     *
     * @param int $id
     * @return InstallmentInterface | null
     * @throws NoSuchEntityException
     */
    public function get(int $id);

    /**
     * Save VendorProduct
     *
     * @param InstallmentProductInterface $installment
     * @return InstallmentInterface
     * @throws CouldNotSaveException
     */
    public function save(InstallmentProductInterface $installment);

    /**
     * Delete VendorProduct
     *
     * @param InstallmentProductInterface $installment
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(InstallmentProductInterface $installment);

    /**
     * Delete VendorProduct by id
     *
     * @param int $id
     * @return bool
     */
    public function deleteById(int $id);

    /**
     * Get VendorProduct list
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchCriteriaInterface[]
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Get empty VendorProduct
     *
     * @return InstallmentProductInterface
     */
    public function newModel(): InstallmentProductInterface;

    /**
     * Gets VendorProduct by groupId
     *
     * @param string $groupId
     * @return InstallmentProductInterface
     */
    public function getByGroupId(string $groupId, string $country = 'si'): ?InstallmentProductInterface;
}
