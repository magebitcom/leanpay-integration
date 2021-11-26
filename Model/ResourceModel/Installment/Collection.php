<?php
declare(strict_types=1);

namespace Leanpay\Payment\Model\ResourceModel\Installment;

use Leanpay\Payment\Api\Data\InstallmentInterface;
use Leanpay\Payment\Model\Installment as Model;
use Leanpay\Payment\Model\ResourceModel\Installment as ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = InstallmentInterface::ENTITY_ID;

    /**
     * Resource initialization
     *
     * @inheridoc
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }

    /**
     * Get all groups
     *
     * @return array
     */
    public function getAllGroups()
    {
        $orderStatement = sprintf('%s %s', InstallmentInterface::LOAN_AMOUNT, 'ASC');
        $whereStatement = sprintf('%s.%s=?', InstallmentInterface::TABLE_NAME, InstallmentInterface::LOAN_AMOUNT);
        $select = $this->getConnection()
            ->select()
            ->from(InstallmentInterface::TABLE_NAME, [InstallmentInterface::GROUP_NAME])
            ->distinct(true)
            ->order($orderStatement);

        return $this->getConnection()->fetchAssoc($select);
    }
}
