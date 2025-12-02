<?php
declare(strict_types=1);

namespace Leanpay\Payment\Model;

use Leanpay\Payment\Model\ResourceModel\InstallmentProduct\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Leanpay\Payment\Api\Data\InstallmentProductInterface;
use Leanpay\Payment\Api\Data\InstallmentProductInterfaceFactory;
use Leanpay\Payment\Api\InstallmentProductRepositoryInterface;
use Leanpay\Payment\Api\Data\InstallmentProductSearchResultsInterfaceFactory;

class InstallmentProductRepository implements InstallmentProductRepositoryInterface
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
     * @var SearchCriteriaBuilderFactory
     */
    private $criteriaBuilderFactory;

    /**
     * @var InstallmentCollectionProcessor
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
     *  InstallmentProductRepository constructor
     *
     * @param ResourceModel\InstallmentProduct $resourceModel
     * @param ResourceModel\InstallmentProduct\CollectionFactory $collectionFactory
     * @param InstallmentProductInterfaceFactory $modelFactory
     */
    public function __construct(
        ResourceModel\InstallmentProduct                   $resourceModel,
        ResourceModel\InstallmentProduct\CollectionFactory $collectionFactory,
        InstallmentProductInterfaceFactory                 $modelFactory,
        SearchCriteriaBuilderFactory                       $criteriaBuilderFactory,
        InstallmentProductSearchResultsInterfaceFactory    $searchResultsFactory,
        ?SearchCriteria                                     $collectionProcessor = null
    ) {
        $this->modelFactory = $modelFactory;
        $this->resourceModel = $resourceModel;
        $this->collectionFactory = $collectionFactory;
        $this->criteriaBuilderFactory = $criteriaBuilderFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
    }

    /**
     * Get VendorProduct by Id
     *
     * @param int $id
     * @return InstallmentInterface|null
     * @throws NoSuchEntityException
     */
    public function get(int $id)
    {
        $model = $this->modelFactory->create();
        $this->resourceModel->load($model, $id);

        if (!$model->getId()) {
            throw new NoSuchEntityException(__('The Installment with the "%1" ID doesn\'t exist.', $id));
        }

        return $model;
    }

    /**
     * Save VendorProduct
     *
     * @param InstallmentInterface $installment
     * @return InstallmentInterface|void
     * @throws CouldNotSaveException
     */
    public function save(InstallmentProductInterface $installment)
    {
        try {
            $this->resourceModel->save($installment);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
    }

    /**
     * Get VendorProduct list
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
     * Delete VendorProduct
     *
     * @param InstallmentInterface $installment
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(InstallmentProductInterface $installment)
    {
        try {
            $this->resourceModel->delete($installment);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }

        return true;
    }

    /**
     * Delete VendorProduct by id
     *
     * @param int $id
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $id)
    {
        return $this->delete($this->get($id));
    }

    /**
     * Get empty VendorProduct
     *
     * @return InstallmentInterface
     */
    public function newModel(): InstallmentProductInterface
    {
        return $this->modelFactory->create();
    }

    /**
     * Gets VendorProduct by groupId
     *
     * @param string $groupId
     * @return InstallmentProductInterface
     */
    public function getByGroupId(string $groupId, string $country = 'si'): ?InstallmentProductInterface
    {
        $criteria = $this->criteriaBuilderFactory->create();
        $criteria = $criteria
            ->addFilter(InstallmentProductInterface::GROUP_ID, $groupId)
            ->addFilter(InstallmentProduct::COUNTRY, $country)
            ->create();

        $result = $this->getList($criteria)->getItems();

        if (empty($result)){
            return null;
        }

        return reset($result);
    }

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
