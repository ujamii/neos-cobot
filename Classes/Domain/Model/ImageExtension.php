<?php

namespace Ujamii\Cobot\Domain\Model;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;
use Neos\Media\Domain\Model\Image;

/**
 * @Flow\Entity
 */
class ImageExtension
{
    /**
     * @Flow\Validate(type="NotEmpty")
     * @ORM\OneToOne
     * @var Image
     */
    protected Image $image;

    /**
     * @var string
     */
    protected string $altText;

    public function getImage(): Image
    {
        return $this->image;
    }

    public function setImage(Image $image): void
    {
        $this->image = $image;
    }

    public function getAltText(): string
    {
        return $this->altText;
    }

    public function setAltText(string $altText): void
    {
        $this->altText = $altText;
    }
}
