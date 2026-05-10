<?php

declare(strict_types=1);

namespace Maispace\MaiSurvey\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class Submission extends AbstractEntity
{
    protected ?Survey $survey = null;

    protected string $sessionHash = '';

    protected int $feUserUid = 0;

    protected ?\DateTime $submittedAt = null;

    /**
     * @var ObjectStorage<Answer>
     */
    protected ObjectStorage $answers;

    public function __construct()
    {
        $this->answers = new ObjectStorage();
    }

    public function initializeObject(): void
    {
        $this->answers = new ObjectStorage();
    }

    public function getSurvey(): ?Survey
    {
        return $this->survey;
    }

    public function setSurvey(?Survey $survey): void
    {
        $this->survey = $survey;
    }

    public function getSessionHash(): string
    {
        return $this->sessionHash;
    }

    public function setSessionHash(string $sessionHash): void
    {
        $this->sessionHash = $sessionHash;
    }

    public function getFeUserUid(): int
    {
        return $this->feUserUid;
    }

    public function setFeUserUid(int $feUserUid): void
    {
        $this->feUserUid = $feUserUid;
    }

    public function getSubmittedAt(): ?\DateTime
    {
        return $this->submittedAt;
    }

    public function setSubmittedAt(?\DateTime $submittedAt): void
    {
        $this->submittedAt = $submittedAt;
    }

    public function addAnswer(Answer $answer): void
    {
        $this->answers->attach($answer);
    }

    public function removeAnswer(Answer $answer): void
    {
        $this->answers->detach($answer);
    }

    /**
     * @return ObjectStorage<Answer>
     */
    public function getAnswers(): ObjectStorage
    {
        return $this->answers;
    }

    /**
     * @param ObjectStorage<Answer> $answers
     */
    public function setAnswers(ObjectStorage $answers): void
    {
        $this->answers = $answers;
    }
}
