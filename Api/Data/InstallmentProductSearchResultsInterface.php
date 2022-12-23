<?php
namespace Leanpay\Payment\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for cms page search results.
 * @api
 * @since 100.0.2
 */
interface InstallmentProductSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get pages list.
     *
     * @return InstallmentProductInterface[]
     */
    public function getItems();

    /**
     * Set pages list.
     *
     * @param InstallmentProductInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
