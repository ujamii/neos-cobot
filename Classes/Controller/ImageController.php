<?php

namespace Ujamii\Cobot\Controller;

use Neos\Flow\Http\Client\Browser;
use Neos\Flow\Http\Client\CurlEngine;
use Neos\Flow\Http\Client\InfiniteRedirectionException;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Mvc\View\JsonView;
use Neos\Flow\Persistence\Exception\IllegalObjectTypeException;
use Neos\Flow\ResourceManagement\Exception as ResourceManagementException;
use Neos\Flow\ResourceManagement\ResourceManager;
use Neos\Flow\Security\Context as SecurityContext;
use Neos\Media\Domain\Model\AssetCollection;
use Neos\Media\Domain\Model\Image;
use Neos\Media\Domain\Repository\AssetCollectionRepository;
use Neos\Media\Domain\Repository\ImageRepository;

class ImageController extends ActionController
{
    private const string ASSET_COLLECTION_NAME = 'Cobot';

    public $viewObjectNamePattern = JsonView::class;

    public function __construct(
        private readonly SecurityContext $securityContext,
        private readonly Browser $browser,
        private readonly ImageRepository $imageRepository,
        private readonly ResourceManager $resourceManager,
        private readonly AssetCollectionRepository $assetCollectionRepository,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function uploadAction(): void
    {
        $this->securityContext->withoutAuthorizationChecks(function () {
            /** @var array<string, string> $content */
            $content = $this->request->getHttpRequest()->getParsedBody();
            $imageUri = $content['imageUri'];

            $image = $this->importImage($imageUri);

            $this->view->assign('value', ['image' => $image->getIdentifier()]);

            $this->logger->info(__CLASS__.'::'.__FUNCTION__.': '.$imageUri);
        });
    }

    /**
     * @throws InfiniteRedirectionException|IllegalObjectTypeException|ResourceManagementException
     */
    private function importImage(string $url): Image
    {
        $this->initializeBrowser();

        $binaryContent = $this->browser->request($url)->getBody()->getContents();
        $imageName = substr(md5($binaryContent), 0, 10);
        /** @phpstan-ignore-next-line  */
        $existingImage = $this->imageRepository->findOneByTitle($imageName);

        if (null !== $existingImage) {
            return $existingImage;
        }

        $filename = $this->getFilenameFromUrl($url);
        $resource = $this->resourceManager->importResourceFromContent($binaryContent, $filename);
        /** @var Image|null $image */
        $image = $this->imageRepository->findOneByResourceSha1($resource->getSha1());

        if (null === $image) {
            $image = new Image($resource);
            $image->setTitle($imageName);

            /** @phpstan-ignore-next-line  */
            $assetCollection = $this->assetCollectionRepository->findOneByTitle(self::ASSET_COLLECTION_NAME);
            if (null === $assetCollection) {
                $assetCollection = new AssetCollection(self::ASSET_COLLECTION_NAME);
                $this->assetCollectionRepository->add($assetCollection);
            }
            $assetCollection->addAsset($image);
            $this->assetCollectionRepository->update($assetCollection);

            $this->imageRepository->update($image);
            $this->persistenceManager->persistAll();
        }

        return $image;
    }

    private function getFilenameFromUrl(string $url): string
    {
        $path = (string) parse_url($url, PHP_URL_PATH);

        return pathinfo($path, PATHINFO_BASENAME);
    }

    private function initializeBrowser(): void
    {
        $engine = new CurlEngine();
        $engine->setOption(CURLOPT_SSL_VERIFYPEER, false);
        $engine->setOption(CURLOPT_TIMEOUT, 60);
        $this->browser->setRequestEngine($engine);
    }
}
