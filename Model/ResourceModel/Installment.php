<?php
declare(strict_types=1);

namespace Leanpay\Payment\Model\ResourceModel;

use Leanpay\Payment\Api\Data\InstallmentInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Installment
 *
 * @package Leanpay\Payment\Model\ResourceModel
 */
class Installment extends AbstractDb
{
    /**
     * @inheridoc
     */
    protected function _construct()
    {
        $this->_init(InstallmentInterface::TABLE_NAME, InstallmentInterface::ENTITY_ID);
    }
}
