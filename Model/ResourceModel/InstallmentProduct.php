<?php
declare(strict_types=1);

namespace Leanpay\Payment\Model\ResourceModel;

use Leanpay\Payment\Api\Data\InstallmentProductInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class InstallmentProduct extends AbstractDb
{
    /**
     * Resource initialization
     *
     * @inheridoc
     */
    protected function _construct()
    {
        $this->_init(
            InstallmentProductInterface::TABLE_NAME,
            InstallmentProductInterface::ENTITY_ID
        );
    }
}
