<?php

namespace Ujamii\Cobot\Domain\Model;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Security\Account;

/**
 * @Flow\Entity
 */
class AccountExtension
{
    /**
     * @Flow\Validate(type="NotEmpty")
     * @ORM\OneToOne
     * @var Account
     */
    protected Account $account;

    /**
     * @var string
     */
    protected string $textGenerationModel;

    /**
     * @var string
     */
    protected string $insertMode;

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function setAccount(Account $account): void
    {
        $this->account = $account;
    }

    public function getTextGenerationModel(): string
    {
        return $this->textGenerationModel;
    }

    public function setTextGenerationModel(string $textGenerationModel): void
    {
        $this->textGenerationModel = $textGenerationModel;
    }

    public function getInsertMode(): string
    {
        return $this->insertMode;
    }

    public function setInsertMode(string $insertMode): void
    {
        $this->insertMode = $insertMode;
    }

    public function toArray(): array
    {
        return [
            'textGenerationModel' => $this->textGenerationModel,
            'insertMode' => $this->insertMode
        ];
    }
}
