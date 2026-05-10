<?php

declare(strict_types=1);

namespace Maispace\MaiSurvey\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Question extends AbstractEntity
{
    protected string $question = '';

    protected string $type = 'single';

    protected bool $required = false;

    protected string $options = '';

    protected int $scaleMin = 1;

    protected int $scaleMax = 5;

    protected ?Survey $survey = null;

    public function getQuestion(): string
    {
        return $this->question;
    }

    public function setQuestion(string $question): void
    {
        $this->question = $question;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): void
    {
        $this->required = $required;
    }

    public function getOptions(): string
    {
        return $this->options;
    }

    /**
     * @return array<int, string>
     */
    public function getOptionsDecoded(): array
    {
        if ($this->options === '' || $this->options === '[]') {
            return [];
        }

        $decoded = json_decode($this->options, true);

        return is_array($decoded) ? array_values(array_filter($decoded, 'is_string')) : [];
    }

    public function setOptions(string $options): void
    {
        $this->options = $options;
    }

    public function getScaleMin(): int
    {
        return $this->scaleMin;
    }

    public function setScaleMin(int $scaleMin): void
    {
        $this->scaleMin = $scaleMin;
    }

    public function getScaleMax(): int
    {
        return $this->scaleMax;
    }

    public function setScaleMax(int $scaleMax): void
    {
        $this->scaleMax = $scaleMax;
    }

    public function getSurvey(): ?Survey
    {
        return $this->survey;
    }

    public function setSurvey(?Survey $survey): void
    {
        $this->survey = $survey;
    }
}
