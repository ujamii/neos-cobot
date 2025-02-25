<?php

namespace Ujamii\Cobot\Filter;

use Neos\Flow\Persistence\Exception\InvalidQueryException;
use Neos\Flow\Persistence\QueryInterface;
use Neos\Media\Domain\Model\Tag;
use Ujamii\Cobot\Filter\Enum\TagFilterType;

class TagFilter {
    /**
     * @throws InvalidQueryException
     */
    public static function applyFilter(QueryInterface $query, TagFilterType $type, ?Tag $tag = null): void
    {
        switch ($type) {
            case TagFilterType::SELECTED:
                $query->matching($query->contains('tags', $tag));
                break;
            case TagFilterType::WITHOUT:
                $query->matching($query->isEmpty('tags'));
                break;
            default:
                break;
        }
    }
}
