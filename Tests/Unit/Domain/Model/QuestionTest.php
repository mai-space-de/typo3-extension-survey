<?php

declare(strict_types=1);

namespace Maispace\MaiSurvey\Tests\Unit\Domain\Model;

use Maispace\MaiSurvey\Domain\Model\Question;
use PHPUnit\Framework\TestCase;

class QuestionTest extends TestCase
{
    private Question $subject;

    protected function setUp(): void
    {
        $this->subject = new Question();
    }

    public function testDefaultTypeIsSingle(): void
    {
        self::assertSame('single', $this->subject->getType());
    }

    public function testDefaultScaleMinIsOne(): void
    {
        self::assertSame(1, $this->subject->getScaleMin());
    }

    public function testDefaultScaleMaxIsFive(): void
    {
        self::assertSame(5, $this->subject->getScaleMax());
    }

    public function testGetOptionsDecodedReturnsEmptyArrayWhenOptionsIsEmpty(): void
    {
        self::assertSame([], $this->subject->getOptionsDecoded());
    }

    public function testGetOptionsDecodedReturnsEmptyArrayWhenOptionsIsEmptyJsonArray(): void
    {
        $this->subject->setOptions('[]');

        self::assertSame([], $this->subject->getOptionsDecoded());
    }

    public function testGetOptionsDecodedReturnsDecodedStringArray(): void
    {
        $this->subject->setOptions('["Option A","Option B","Option C"]');

        self::assertSame(['Option A', 'Option B', 'Option C'], $this->subject->getOptionsDecoded());
    }

    public function testGetOptionsDecodedFiltersNonStringValues(): void
    {
        $this->subject->setOptions('["Valid",42,null,"Also valid"]');

        self::assertSame(['Valid', 'Also valid'], $this->subject->getOptionsDecoded());
    }

    public function testGetOptionsDecodedReturnsEmptyArrayOnInvalidJson(): void
    {
        $this->subject->setOptions('not-json');

        self::assertSame([], $this->subject->getOptionsDecoded());
    }

    public function testSettersAndGetters(): void
    {
        $this->subject->setQuestion('What is your favourite colour?');
        $this->subject->setType('multi');
        $this->subject->setRequired(true);
        $this->subject->setScaleMin(0);
        $this->subject->setScaleMax(10);

        self::assertSame('What is your favourite colour?', $this->subject->getQuestion());
        self::assertSame('multi', $this->subject->getType());
        self::assertTrue($this->subject->isRequired());
        self::assertSame(0, $this->subject->getScaleMin());
        self::assertSame(10, $this->subject->getScaleMax());
    }
}
