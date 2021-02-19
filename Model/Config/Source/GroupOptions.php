<?php
declare(strict_types=1);

namespace Leanpay\Payment\Model\Config\Source;

use Leanpay\Payment\Model\ResourceModel\Installment\Collection;
use Leanpay\Payment\Model\ResourceModel\Installment\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class ViewBlockConfig
 *
 * @package Leanpay\Payment\Model\Config\Source
 */
class GroupOptions implements OptionSourceInterface
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * GroupSelector constructor.
     *
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collection = $collectionFactory->create();
    }

    /**
     * Return array of leanpay environment
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $groups = $this->collection->getAllGroups();

        $options[] = [
            'value' => 'SPLET - REDNA PONUDBA',
            'label' => __('Default')
        ];

        foreach ($groups as $key => $id) {
            $options[] = [
                'value' => $id['group_name'],
                'label' => $id['group_name']
            ];
        }

        return $options;
    }
}
