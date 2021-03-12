<?php
/**
 * Copyright © Landofcoder All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Lof\BrandGraphQl\Model\Resolver;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Lof\BrandGraphQl\Model\Resolver\Query\Products\ProductQueryInterface;

/**
 * Class ProductByBrand
 * @package Lof\BrandGraphQl\Model\Resolver
 */
class ProductByBrand implements ResolverInterface
{
    /**
     * @var ProductQueryInterface
     */
    private $searchQuery;

    /**
     * ProductByBrand constructor.
     * @param ProductQueryInterface $searchQuery
     */
    public function __construct(
        ProductQueryInterface $searchQuery
    )
    {
        $this->searchQuery = $searchQuery;
    }

    /**
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array|Value|mixed
     * @throws GraphQlInputException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    )
    {
        if ($args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }
        if ($args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }
        if (!isset($args['brand_id'])) {
            throw new GraphQlInputException(
                __("'brand_id' input argument is required.")
            );
        }

        $searchResult = $this->searchQuery->getResult($args, $info, $context);

        if ($searchResult->getCurrentPage() > $searchResult->getTotalPages() && $searchResult->getTotalCount() > 0) {
            throw new GraphQlInputException(
                __(
                    'currentPage value %1 specified is greater than the %2 page(s) available.',
                    [$searchResult->getCurrentPage(), $searchResult->getTotalPages()]
                )
            );
        }

        return [
            'total_count' => $searchResult->getTotalCount(),
            'items' => $searchResult->getProductsSearchResult(),
            'page_info' => [
                'page_size' => $searchResult->getPageSize(),
                'current_page' => $searchResult->getCurrentPage(),
                'total_pages' => $searchResult->getTotalPages()
            ],
            'search_result' => $searchResult,
            'layer_type' => isset($args['search']) ? Resolver::CATALOG_LAYER_SEARCH : Resolver::CATALOG_LAYER_CATEGORY,
        ];
    }
}
