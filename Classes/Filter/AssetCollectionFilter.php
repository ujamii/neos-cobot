<?php

namespace Ujamii\Cobot\Filter;

use Neos\Flow\Persistence\Exception\InvalidQueryException;
use Neos\Flow\Persistence\QueryInterface;
use Neos\Media\Domain\Model\AssetCollection;
use Ujamii\Cobot\Filter\Enum\AssetCollectionFilterType;

class AssetCollectionFilter
{
    /**
     * @throws InvalidQueryException
     */
    public static function applyFilter(QueryInterface $query, AssetCollectionFilterType $type, ?AssetCollection $assetCollection = null): void
    {
        switch ($type) {
            case AssetCollectionFilterType::SELECTED:
                $query->matching($query->contains('assetCollections', $assetCollection));
                break;
            case AssetCollectionFilterType::WITHOUT:
                $query->matching($query->isEmpty('assetCollections'));
                break;
            default:
                break;
        }
    }
}
