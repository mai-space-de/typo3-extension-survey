<?php

declare(strict_types=1);

namespace Maispace\MaiSurvey\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Answer extends AbstractEntity
{
    protected ?Submission $submission = null;

    protected ?Question $question = null;

    protected string $value = '';

    public function getSubmission(): ?Submission
    {
        return $this->submission;
    }

    public function setSubmission(?Submission $submission): void
    {
        $this->submission = $submission;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): void
    {
        $this->question = $question;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }
}
