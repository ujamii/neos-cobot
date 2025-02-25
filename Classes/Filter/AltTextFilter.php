<?php

namespace Ujamii\Cobot\Filter;

use Neos\Flow\Persistence\QueryInterface;
use Ujamii\Cobot\Domain\Repository\ImageExtensionRepository;
use Ujamii\Cobot\Filter\Enum\AltTextFilterType;

class AltTextFilter
{
    public function __construct(private readonly ImageExtensionRepository $imageExtensionRepository)
    {
    }

    public function applyFilter(QueryInterface $query, AltTextFilterType $type): void
    {
        $imageExtensionsQuery = $this->imageExtensionRepository->createQuery();
        $imageExtensions = $imageExtensionsQuery->matching(
            $imageExtensionsQuery->logicalNot(
                $imageExtensionsQuery->equals('altText', '')
            ))
            ->execute()
            ->toArray();

        $imageIds = array_map(fn($ext) => $ext->getImage()->getIdentifier(), $imageExtensions);

        switch ($type) {
            case AltTextFilterType::ALT_TEXT:
                $query->matching($query->in('Persistence_Object_Identifier', $imageIds));
                break;
            case AltTextFilterType::WITHOUT_ALT_TEXT:
                $query->matching($query->logicalNot($query->in('Persistence_Object_Identifier', $imageIds)));
                break;
            default:
                break;
        }
    }
}
