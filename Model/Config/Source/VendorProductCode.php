<?php
declare(strict_types=1);

namespace Leanpay\Payment\Model\Config\Source;

use Leanpay\Payment\Api\InstallmentProductRepositoryInterface;
use Leanpay\Payment\Helper\Data;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * VendorProductCode
 */
class VendorProductCode implements OptionSourceInterface
{
    private \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder;
    private InstallmentProductRepositoryInterface $installmentProductRepository;

    /***
     * @param InstallmentProductRepositoryInterface $installmentProductRepository
     * @param SearchCriteriaBuilderFactory $criteriaBuilderFactory
     */
    public function __construct(
        InstallmentProductRepositoryInterface $installmentProductRepository,
        SearchCriteriaBuilderFactory $criteriaBuilderFactory
    ) {
        $this->installmentProductRepository = $installmentProductRepository;
        $this->criteriaBuilder = $criteriaBuilderFactory->create();
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $result = [
            'value' => '',
            'label' => ''
        ];

        $items = $this->installmentProductRepository->getList($this->criteriaBuilder->create())->getItems();

        if (empty($items)) {
            return $result;
        }

        foreach ($items as $index => $item) {
            $result[$index] = [
                'value' => $item->getGroupId(),
                'label' => $item->getGroupName()
            ];
        }

        return $result;
    }
}
