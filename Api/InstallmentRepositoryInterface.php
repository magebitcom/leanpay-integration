<?php
declare(strict_types=1);

namespace Leanpay\Payment\Api;

use Leanpay\Payment\Api\Data\InstallmentInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface InstallmentRepositoryInterface
{
    /**
     * @param int|string $id
     * @return InstallmentInterface | null
     * @throws NoSuchEntityException
     */
    public function get($id);

    /**
     * @param InstallmentInterface $installment
     * @return InstallmentInterface
     * @throws CouldNotSaveException
     */
    public function save(InstallmentInterface $installment);

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchCriteriaInterface[]
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param InstallmentInterface $installment
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(InstallmentInterface $installment);

    /**
     * @param int|string $id
     * @return bool
     */
    public function deleteById($id);

    /**
     * @return InstallmentInterface
     */
    public function newModel();
}
