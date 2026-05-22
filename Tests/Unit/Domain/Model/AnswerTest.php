<?php

declare(strict_types=1);

namespace Maispace\MaiSurvey\Tests\Unit\Domain\Model;

use Maispace\MaiSurvey\Domain\Model\Answer;
use Maispace\MaiSurvey\Domain\Model\Question;
use Maispace\MaiSurvey\Domain\Model\Submission;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AnswerTest extends TestCase
{
    private Answer $subject;

    protected function setUp(): void
    {
        $this->subject = new Answer();
    }

    #[Test]
    public function defaultValueIsEmpty(): void
    {
        self::assertSame('', $this->subject->getValue());
    }

    #[Test]
    public function defaultSubmissionIsNull(): void
    {
        self::assertNull($this->subject->getSubmission());
    }

    #[Test]
    public function defaultQuestionIsNull(): void
    {
        self::assertNull($this->subject->getQuestion());
    }

    #[Test]
    public function setAndGetValue(): void
    {
        $this->subject->setValue('Option A');

        self::assertSame('Option A', $this->subject->getValue());
    }

    #[Test]
    public function setAndGetQuestion(): void
    {
        $question = new Question();
        $question->setQuestion('What is your name?');
        $this->subject->setQuestion($question);

        self::assertSame($question, $this->subject->getQuestion());
        self::assertSame('What is your name?', $this->subject->getQuestion()->getQuestion());
    }

    #[Test]
    public function setQuestionToNull(): void
    {
        $question = new Question();
        $this->subject->setQuestion($question);
        $this->subject->setQuestion(null);

        self::assertNull($this->subject->getQuestion());
    }

    #[Test]
    public function setAndGetSubmission(): void
    {
        $submission = new Submission();
        $submission->setSessionHash('abc123');
        $this->subject->setSubmission($submission);

        self::assertSame($submission, $this->subject->getSubmission());
        self::assertSame('abc123', $this->subject->getSubmission()->getSessionHash());
    }

    #[Test]
    public function setSubmissionToNull(): void
    {
        $submission = new Submission();
        $this->subject->setSubmission($submission);
        $this->subject->setSubmission(null);

        self::assertNull($this->subject->getSubmission());
    }

    #[Test]
    public function twoIndependentInstancesDoNotShareState(): void
    {
        $other = new Answer();
        $other->setValue('different');

        self::assertSame('', $this->subject->getValue());
        self::assertSame('different', $other->getValue());
    }
}
