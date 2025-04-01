<?php

namespace Ujamii\Cobot\Controller;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Mvc\View\JsonView;
use Neos\Flow\Security\Context as SecurityContext;
use Ujamii\Cobot\Domain\Model\AccountExtension;
use Ujamii\Cobot\Domain\Repository\AccountExtensionRepository;

class UserSettingsController extends ActionController
{
    private const array DEFAULT_SETTINGS = [
        'textGenerationModel' => 'gpt-4o-mini',
        'insertMode' => 'stream'
    ];

    protected $defaultViewObjectName = JsonView::class;

    public function __construct(
        private readonly SecurityContext $securityContext,
        private readonly AccountExtensionRepository $accountExtensionRepository
    )
    {
    }

    public function getAction(): void
    {
        $account = $this->securityContext->getAccount();
        /** @var AccountExtension $accountExtension */
        $accountExtension = $this->accountExtensionRepository->findOneByAccount($account);
        if(null === $account || null === $accountExtension) {
            $this->view->assign('value', self::DEFAULT_SETTINGS);
            return;
        }

        $this->view->assign('value', array_merge(
            self::DEFAULT_SETTINGS,
            $accountExtension->toArray()
        ));
    }

    public function updateAction(): void
    {
        $account = $this->securityContext->getAccount();
        /** @var AccountExtension $accountExtension */
        $accountExtension = $this->accountExtensionRepository->findOneByAccount($account);

        if(null === $account) {
            throw new \Exception('No account found');
        }

        if(null === $accountExtension) {
            $accountExtension = new AccountExtension();
            $accountExtension->setAccount($account);
        }

        $accountExtension->setTextGenerationModel($this->request->getArgument('textGenerationModel'));
        $accountExtension->setInsertMode($this->request->getArgument('insertMode'));

        if($this->persistenceManager->isNewObject($accountExtension)) {
            $this->accountExtensionRepository->add($accountExtension);
        } else {
            $this->accountExtensionRepository->update($accountExtension);
        }

        $this->persistenceManager->persistAll();

        $this->response->setStatusCode(204);
    }
}
