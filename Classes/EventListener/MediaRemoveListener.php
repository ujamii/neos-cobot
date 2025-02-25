<?php

namespace Ujamii\Cobot\EventListener;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\Doctrine\PersistenceManager;
use Neos\Media\Domain\Model\Image;
use Ujamii\Cobot\Domain\Repository\ImageExtensionRepository;

/**
 * @Flow\Scope("singleton")
 */
class MediaRemoveListener
{
    public function __construct(
        private readonly ImageExtensionRepository $imageExtensionRepository,
        private readonly PersistenceManager $persistenceManager
    )
    {
    }

    public function preRemove(LifecycleEventArgs $eventArguments): void
    {
        $entity = $eventArguments->getObject();
        if ($entity instanceof Image) {
            $imageExtension = $this->imageExtensionRepository->findOneByImage($entity);

            if ($imageExtension !== null) {
                $this->imageExtensionRepository->remove($imageExtension);
            }
        }
    }
}
