<?php

declare(strict_types=1);

namespace Leanpay\Payment\Model;

use Leanpay\Payment\Api\Data\InstallmentProductSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Service Data Object with Installment search results.
 */
class InstallmentProductSearchResults extends SearchResults implements InstallmentProductSearchResultsInterface
{
}
