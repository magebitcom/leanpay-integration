<?php
declare(strict_types=1);

namespace Leanpay\Payment\Model;

use Magento\Framework\App\ObjectManager;
use Leanpay\Payment\Api\Data\InstallmentInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Leanpay\Payment\Api\InstallmentRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Leanpay\Payment\Api\Data\InstallmentInterfaceFactory;
use Leanpay\Payment\Api\Data\InstallmentSearchResultsInterfaceFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;

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

    /**
     * @var PageCollectionProcessor|CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var CollectionFactory
     */
    private  $collectionFactory;

    /**
     * @var InstallmentProductSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * InstallmentRepository constructor.
     *
     * @param ResourceModel\Installment $resourceModel
     * @param ResourceModel\Installment\CollectionFactory $collectionFactory
     * @param InstallmentInterfaceFactory $modelFactory
     */
    public function __construct(
        ResourceModel\Installment $resourceModel,
        ResourceModel\Installment\CollectionFactory $collectionFactory,
        InstallmentInterfaceFactory $modelFactory,
        InstallmentSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->modelFactory = $modelFactory;
        $this->resourceModel = $resourceModel;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor ?? $this->getCollectionProcessor();
    }

    /**
     * Get installment by Id
     *
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
     * Save installment
     *
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
     * Get installment list
     *
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
     * Delete installment
     *
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
     * Delete installment by id
     *
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
     * Get empty installment
     *
     * @return InstallmentInterface
     */
    public function newModel()
    {
        return $this->modelFactory->create();
    }

    /**
     * @return PageCollectionProcessor|CollectionProcessorInterface
     */
    private function getCollectionProcessor()
    {
        if (!$this->collectionProcessor) {
            // phpstan:ignore "Class Leanpay\Payment\Model\Api\SearchCriteria\InstallmentCollectionProcessor not found."
            $this->collectionProcessor = ObjectManager::getInstance()
                ->get(\Leanpay\Payment\Model\Api\SearchCriteria\InstallmentCollectionProcessor::class);
        }
        return $this->collectionProcessor;
    }
}
