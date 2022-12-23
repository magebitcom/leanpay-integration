<?php
declare(strict_types=1);

namespace Leanpay\Payment\Model\ResourceModel\InstallmentProduct;

use Leanpay\Payment\Api\Data\InstallmentProductInterface;
use Leanpay\Payment\Model\InstallmentProduct as Model;
use Leanpay\Payment\Model\ResourceModel\InstallmentProduct as ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = InstallmentProductInterface::ENTITY_ID;

    /**
     * Resource initialization
     *
     * @inheridoc
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
