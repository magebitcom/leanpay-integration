<?php
declare(strict_types=1);

namespace Leanpay\Payment\Model\Config\Source;

use Leanpay\Payment\Api\InstallmentProductRepositoryInterface;
use Leanpay\Payment\Helper\Data;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;

class VendorAttribute extends AbstractSource
{
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
    public function getAllOptions(): array
    {
        if (!$this->_options) {
            $this->_options[0] = [
                'value' => '',
                'label' => '-- None --'
            ];

            $items = $this->installmentProductRepository->getList($this->criteriaBuilder->create())->getItems();

            if (empty($items)) {
                return $this->_options;
            }

            foreach ($items as $index => $item) {
                $this->_options[$index] = [
                    'value' => $item->getGroupId(),
                    'label' => $item->getGroupName()
                ];
            }
        }

        return $this->_options;
    }
}
