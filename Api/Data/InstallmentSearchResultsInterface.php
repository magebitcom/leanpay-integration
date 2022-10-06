<?php
namespace Leanpay\Payment\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for cms page search results.
 * @api
 * @since 100.0.2
 */
interface InstallmentSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get pages list.
     *
     * @return InstallmentInterface[]
     */
    public function getItems();

    /**
     * Set pages list.
     *
     * @param InstallmentInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
