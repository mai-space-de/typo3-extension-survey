<?php

declare(strict_types=1);

namespace Maispace\MaiSurvey\Tests\Unit\Domain\Model;

use Maispace\MaiSurvey\Domain\Model\Answer;
use Maispace\MaiSurvey\Domain\Model\Submission;
use PHPUnit\Framework\TestCase;

class SubmissionTest extends TestCase
{
    private Submission $subject;

    protected function setUp(): void
    {
        $this->subject = new Submission();
    }

    public function testDefaultSessionHashIsEmpty(): void
    {
        self::assertSame('', $this->subject->getSessionHash());
    }

    public function testDefaultFeUserUidIsZero(): void
    {
        self::assertSame(0, $this->subject->getFeUserUid());
    }

    public function testDefaultSubmittedAtIsNull(): void
    {
        self::assertNull($this->subject->getSubmittedAt());
    }

    public function testAnswersObjectStorageIsInitialized(): void
    {
        self::assertCount(0, $this->subject->getAnswers());
    }

    public function testAddAnswerIncreasesCount(): void
    {
        $answer = new Answer();
        $this->subject->addAnswer($answer);

        self::assertCount(1, $this->subject->getAnswers());
    }

    public function testRemoveAnswerDecreasesCount(): void
    {
        $answer = new Answer();
        $this->subject->addAnswer($answer);
        $this->subject->removeAnswer($answer);

        self::assertCount(0, $this->subject->getAnswers());
    }

    public function testSettersAndGetters(): void
    {
        $date = new \DateTime('2026-01-01 12:00:00');
        $this->subject->setSessionHash('abc123');
        $this->subject->setFeUserUid(42);
        $this->subject->setSubmittedAt($date);

        self::assertSame('abc123', $this->subject->getSessionHash());
        self::assertSame(42, $this->subject->getFeUserUid());
        self::assertSame($date, $this->subject->getSubmittedAt());
    }
}
