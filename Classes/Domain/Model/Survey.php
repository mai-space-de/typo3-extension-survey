<?php

declare(strict_types=1);

namespace Maispace\MaiSurvey\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class Survey extends AbstractEntity
{
    protected string $title = '';

    protected string $description = '';

    protected bool $isActive = false;

    protected bool $allowAnonymous = true;

    protected bool $preventDuplicates = true;

    protected string $successMessage = '';

    /**
     * @var ObjectStorage<Question>
     */
    protected ObjectStorage $questions;

    public function __construct()
    {
        $this->questions = new ObjectStorage();
    }

    public function initializeObject(): void
    {
        $this->questions = new ObjectStorage();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function isAllowAnonymous(): bool
    {
        return $this->allowAnonymous;
    }

    public function setAllowAnonymous(bool $allowAnonymous): void
    {
        $this->allowAnonymous = $allowAnonymous;
    }

    public function isPreventDuplicates(): bool
    {
        return $this->preventDuplicates;
    }

    public function setPreventDuplicates(bool $preventDuplicates): void
    {
        $this->preventDuplicates = $preventDuplicates;
    }

    public function getSuccessMessage(): string
    {
        return $this->successMessage;
    }

    public function setSuccessMessage(string $successMessage): void
    {
        $this->successMessage = $successMessage;
    }

    /**
     * @return ObjectStorage<Question>
     */
    public function getQuestions(): ObjectStorage
    {
        return $this->questions;
    }

    /**
     * @param ObjectStorage<Question> $questions
     */
    public function setQuestions(ObjectStorage $questions): void
    {
        $this->questions = $questions;
    }
}
