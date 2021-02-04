<?php
declare(strict_types=1);

namespace Leanpay\Payment\Model\ResourceModel\Installment;

use Leanpay\Payment\Api\Data\InstallmentInterface;
use Leanpay\Payment\Model\Installment as Model;
use Leanpay\Payment\Model\ResourceModel\Installment as ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 *
 * @package Leanpay\Payment\Model\ResourceModel\Installment
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = InstallmentInterface::ENTITY_ID;

    /**
     * @inheridoc
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
