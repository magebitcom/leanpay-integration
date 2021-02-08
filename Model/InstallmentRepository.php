<?php
declare(strict_types=1);

namespace Leanpay\Payment\Model;

use Leanpay\Payment\Api\Data\InstallmentInterface;
use Leanpay\Payment\Api\Data\InstallmentInterfaceFactory;
use Leanpay\Payment\Api\InstallmentRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class InstallmentRepository
 *
 * @package Leanpay\Payment\Model
 */
class InstallmentRepository implements InstallmentRepositoryInterface
{
    /**
     * @var ResourceModel\Installment
     */
    private $resourceModel;

    /**
     * @var InstallmentInterfaceFactory
     */
    private $modelFactory;

    public function __construct(
        ResourceModel\Installment $resourceModel,
        ResourceModel\Installment\CollectionFactory $collectionFactory,
        InstallmentInterfaceFactory $modelFactory
    ) {
        $this->modelFactory = $modelFactory;
        $this->resourceModel = $resourceModel;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param int|string $id
     * @return InstallmentInterface|null
     * @throws NoSuchEntityException
     */
    public function get($id)
    {
        $model = $this->modelFactory->create();
        $this->resourceModel->load($model, $id);

        if (!$model->getId()) {
            throw new NoSuchEntityException(__('The Installment with the "%1" ID doesn\'t exist.', $id));
        }

        return $model;
    }

    /**
     * @param InstallmentInterface $installment
     * @return InstallmentInterface|void
     * @throws CouldNotSaveException
     */
    public function save(InstallmentInterface $installment)
    {
        try {
            $this->resourceModel->save($installment);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchCriteriaInterface[]
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * @param InstallmentInterface $installment
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(InstallmentInterface $installment)
    {
        try {
            $this->resourceModel->delete($installment);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }

        return true;
    }

    /**
     * @param int|string $id
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($id)
    {
        return $this->delete($this->get($id));
    }

    /**
     * @return InstallmentInterface
     */
    public function newModel()
    {
        return $this->modelFactory->create();
    }
}
