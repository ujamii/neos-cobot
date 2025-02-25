<?php

declare(strict_types=1);

namespace Ujamii\Cobot\Eel;

use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Media\Domain\Model\ImageInterface;
use Ujamii\Cobot\Domain\Repository\ImageExtensionRepository;

class ImageExtensionHelper implements ProtectedContextAwareInterface
{
    public function __construct(private readonly ImageExtensionRepository $imageExtensionRepository)
    {
    }

    /**
     * Returns the alt text of an image from the \Ujamii\Cobot\Domain\Model\ImageExtension::class.
     *
     * @param ImageInterface $image The image to get the alt text from
     * @return string The alt text of the image or an empty string if no alt text is set
     */
    public function getAltText(ImageInterface $image): string
    {
        /* @phpstan-ignore-next-line */
        $imageExtension = $this->imageExtensionRepository->findOneByImage($image);

        return $imageExtension ? $imageExtension->getAltText() : '';
    }

    public function hasAltText(ImageInterface $image): bool
    {
        /* @phpstan-ignore-next-line */
        $imageExtension = $this->imageExtensionRepository->findOneByImage($image);

        return $imageExtension !== null && $imageExtension->getAltText() !== '';
    }

    public function allowsCallOfMethod($methodName): bool
    {
        return true;
    }
}
