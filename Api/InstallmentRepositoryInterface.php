<?php

namespace Leanpay\Payment\Api;

use Leanpay\Payment\Api\Data\InstallmentInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface InstallmentRepositoryInterface
{
    /**
     * Get installment by id
     *
     * @param int|string $id
     * @return InstallmentInterface | null
     * @throws NoSuchEntityException
     */
    public function get($id);

    /**
     * Save installment
     *
     * @param InstallmentInterface $installment
     * @return InstallmentInterface
     * @throws CouldNotSaveException
     */
    public function save(InstallmentInterface $installment);

    /**
     * Get installment list
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchCriteriaInterface[]
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete installment
     *
     * @param InstallmentInterface $installment
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(InstallmentInterface $installment);

    /**
     * Delete installment by id
     *
     * @param int|string $id
     * @return bool
     */
    public function deleteById($id);

    /**
     * Get empty installment
     *
     * @return InstallmentInterface
     */
    public function newModel();
}
