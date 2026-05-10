<?php

declare(strict_types=1);

namespace Maispace\MaiSurvey\Tests\Unit\Domain\Model;

use Maispace\MaiSurvey\Domain\Model\Survey;
use PHPUnit\Framework\TestCase;

class SurveyTest extends TestCase
{
    private Survey $subject;

    protected function setUp(): void
    {
        $this->subject = new Survey();
    }

    public function testDefaultTitleIsEmpty(): void
    {
        self::assertSame('', $this->subject->getTitle());
    }

    public function testDefaultIsActiveFalse(): void
    {
        self::assertFalse($this->subject->isActive());
    }

    public function testDefaultAllowAnonymousTrue(): void
    {
        self::assertTrue($this->subject->isAllowAnonymous());
    }

    public function testDefaultPreventDuplicatesTrue(): void
    {
        self::assertTrue($this->subject->isPreventDuplicates());
    }

    public function testQuestionsObjectStorageIsInitialized(): void
    {
        self::assertCount(0, $this->subject->getQuestions());
    }

    public function testSettersAndGetters(): void
    {
        $this->subject->setTitle('Annual satisfaction survey');
        $this->subject->setDescription('Please share your feedback.');
        $this->subject->setIsActive(true);
        $this->subject->setAllowAnonymous(false);
        $this->subject->setPreventDuplicates(true);
        $this->subject->setSuccessMessage('Thank you!');

        self::assertSame('Annual satisfaction survey', $this->subject->getTitle());
        self::assertSame('Please share your feedback.', $this->subject->getDescription());
        self::assertTrue($this->subject->isActive());
        self::assertFalse($this->subject->isAllowAnonymous());
        self::assertTrue($this->subject->isPreventDuplicates());
        self::assertSame('Thank you!', $this->subject->getSuccessMessage());
    }
}
