<?php

declare(strict_types=1);

namespace Leanpay\Payment\Model;

use Leanpay\Payment\Api\Data\InstallmentSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Service Data Object with Installment search results.
 */
class InstallmentSearchResults extends SearchResults implements InstallmentSearchResultsInterface
{
}
