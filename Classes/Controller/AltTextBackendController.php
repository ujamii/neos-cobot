<?php

namespace Ujamii\Cobot\Controller;

use Neos\Error\Messages\Message;
use Neos\Flow\Http\Client\Browser;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Mvc\Exception\StopActionException;
use Neos\Flow\Mvc\View\ViewInterface;
use Neos\Flow\Persistence\Exception\InvalidQueryException;
use Neos\Flow\Persistence\QueryInterface;
use Neos\Fusion\View\FusionView;
use Neos\Media\Domain\Model\AssetCollection;
use Neos\Media\Domain\Model\Image;
use Neos\Media\Domain\Model\Tag;
use Neos\Media\Domain\Repository\AssetCollectionRepository;
use Neos\Media\Domain\Repository\ImageRepository;
use Neos\Media\Domain\Repository\TagRepository;
use Throwable;
use Ujamii\Cobot\Domain\Model\ImageExtension;
use Ujamii\Cobot\Domain\Repository\ImageExtensionRepository;
use Ujamii\Cobot\Filter\AltTextFilter;
use Ujamii\Cobot\Filter\AssetCollectionFilter;
use Ujamii\Cobot\Filter\Enum\AltTextFilterType;
use Ujamii\Cobot\Filter\Enum\AssetCollectionFilterType;
use Ujamii\Cobot\Filter\Enum\TagFilterType;
use Ujamii\Cobot\Filter\TagFilter;

class AltTextBackendController extends ActionController
{
    private const int PAGE_SIZE = 12;

    protected $defaultViewObjectName = FusionView::class;

    public function __construct(
        private readonly ImageExtensionRepository $imageExtensionRepository,
        private readonly ImageRepository $imageRepository,
        private readonly AssetCollectionRepository $assetCollectionRepository,
        private readonly TagRepository $tagRepository,
        private readonly AltTextFilter $altTextFilter,
        private readonly Browser $browser,
    ) {
    }


    protected function initializeView(ViewInterface $view): void
    {
        parent::initializeView($view);

        /* @phpstan-ignore-next-line */
        $view->setFusionPathPattern('resource://Ujamii.Cobot/Private/Fusion');
    }

    /**
     * @throws InvalidQueryException
     */
    public function indexAction(
        int $page = 1,
        ?string $assetCollectionFilter = 'all',
        ?AssetCollection $assetCollection = null,
        ?string $tagFilter = 'all',
        ?Tag $tag = null,
        ?string $altTextFilter = 'all'
    ): void
    {
        $query = $this->imageRepository->createQuery();
        $query->setOrderings(['lastModified' => QueryInterface::ORDER_DESCENDING]);

        $assetCollectionFilterType = AssetCollectionFilterType::from($assetCollectionFilter);
        AssetCollectionFilter::applyFilter($query, $assetCollectionFilterType, $assetCollection);

        $tagCollectionFilterType = TagFilterType::from($tagFilter);
        TagFilter::applyFilter($query, $tagCollectionFilterType, $tag);

        $altTextFilterType = AltTextFilterType::from($altTextFilter);
        $this->altTextFilter->applyFilter($query, $altTextFilterType);

        $imageCount = $query->execute()->count();

        $this->view->assignMultiple([
            'images' => $query->setLimit(self::PAGE_SIZE)->setOffset(($page - 1) * self::PAGE_SIZE)->execute(),
            'pagination' => $this->createPagination($page, $imageCount),
            'filter' => [
                'assetCollectionFilter' => $assetCollectionFilter,
                'tagFilter' => $tagFilter,
                'altTextFilter' => $altTextFilter,
                'assetCollection' => $assetCollection,
                'tag' => $tag,
            ],
            'assetCollections' => $this->assetCollectionRepository->findAll()->toArray(),
            'tags' => $this->tagRepository->findAll()->toArray(),
        ]);
    }

    /**
     * @throws StopActionException
     * @throws \Exception
     */
    public function generateAction(Image $image): void
    {
        $thumbnail = $image->getThumbnail(800, 800);
        $base64 = base64_encode(stream_get_contents($thumbnail->getResource()->getStream()));
        $base64 = 'data:'.$image->getResource()->getMediaType().';base64,'.$base64;

        $uri = $this->settings['services']['baseUri'].$this->settings['services']['routes']['altText']['path'];
        $accessToken = $this->settings['services']['apiKey'];
        try {
            $response = $this->browser->request(
                $uri,
                'POST',
                server: [
                    'HTTP_Content-Type' => 'application/json',
                    'HTTP_Authorization' => 'Bearer '.$accessToken,
                ],
                content: json_encode([
                    'base64' => $base64,
                    'lang' => 'de',
                ])
            );
        } catch (\Throwable $exception) {
            $this->addFlashMessage('Error while generating alt text: ' . $exception->getMessage(), '', Message::SEVERITY_ERROR);
            $this->redirect('index');

            return;
        }

        $content = $response->getBody()->getContents();
        $content = json_decode($content, true);
        $statusCode = $response->getStatusCode();
        if ($statusCode !== 200) {
            $this->logger->error('Error while generating alt text', ['response' => $content]);
            $this->addFlashMessage('Error while generating alt text: ' . $content['message'], '', Message::SEVERITY_ERROR);
            $this->redirect('index');

            return;
        }

        try {
            /* @phpstan-ignore-next-line */
            $existingImageExtension = $this->imageExtensionRepository->findOneByImage($image);
            if (null === $existingImageExtension) {
                $imageExtension = new ImageExtension();
                $imageExtension->setAltText($content['text']);
                $imageExtension->setImage($image);
                $this->persistenceManager->add($imageExtension);
            } else {
                $existingImageExtension->setAltText($content['text']);
                $this->persistenceManager->update($existingImageExtension);
            }

            $this->persistenceManager->persistAll();
            $this->addFlashMessage('Alt text generated successfully');
        } catch (Throwable $exception) {
            $this->logger->error('Error while generating alt text', ['exception' => $exception]);
            $this->addFlashMessage('Error while generating alt text: ' . $exception->getMessage(), '', Message::SEVERITY_ERROR);
        }

        $referer = $this->request->getHttpRequest()->getHeader('referer');
        if ($referer && array_key_exists(0, $referer)) {
            $this->redirectToUri($referer[0]);
        }

        $this->redirect('index');
    }

    public function changeAction(Image $image, string $altText): void
    {
        $altText = trim($altText);

        try {
            /* @phpstan-ignore-next-line */
            $existingImageExtension = $this->imageExtensionRepository->findOneByImage($image);
            if (null === $existingImageExtension) {
                $imageExtension = new ImageExtension();
                $imageExtension->setAltText($altText);
                $imageExtension->setImage($image);
                $this->persistenceManager->add($imageExtension);
            } else {
                $existingImageExtension->setAltText($altText);
                $this->persistenceManager->update($existingImageExtension);
            }

            $this->persistenceManager->persistAll();
            $this->addFlashMessage('Alt text updated successfully');
        } catch (Throwable $exception) {
            $this->logger->error('Error while updating alt text', ['exception' => $exception]);
            $this->addFlashMessage('Error while updating alt text: ' . $exception->getMessage(), '', Message::SEVERITY_ERROR);
        }

        $referer = $this->request->getHttpRequest()->getHeader('referer');
        if ($referer && array_key_exists(0, $referer)) {
            $this->redirectToUri($referer[0]);
        }

        $this->redirect('index');
    }

    /**
     * @return array<string, int|bool|array<int>>
     */
    private function createPagination(int $page, int $count): array
    {
        $totalPages = ceil($count / self::PAGE_SIZE);
        $pages = range(max(1, $page - 4), min($totalPages, $page + 4));

        return [
            'currentPage' => $page,
            'totalPages' => (int) $totalPages,
            'nextPage' => min($totalPages, $page + 1),
            'hasNextPage' => $page + 1 <= $totalPages,
            'previousPage' => max(1, $page - 1),
            'hasPreviousPage' => $page > 1,
            'hasMorePages' => $page + 6 < $totalPages,
            'hasLessPages' => $page > 6,
            'pages' => array_map(fn ($page) => (int)$page, $pages),
        ];
    }
}
