<?php

declare(strict_types=1);

namespace Maispace\MaiSurvey\Tests\Unit\Domain\Repository;

use Maispace\MaiSurvey\Domain\Repository\SubmissionRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Extbase\Persistence\Repository;

final class SubmissionRepositoryTest extends TestCase
{
    #[Test]
    public function repositoryExtendsTYPO3BaseRepository(): void
    {
        self::assertTrue(
            is_subclass_of(SubmissionRepository::class, Repository::class),
            SubmissionRepository::class . ' must extend ' . Repository::class,
        );
    }

    #[Test]
    public function hasNoCustomDefaultOrderings(): void
    {
        $reflection = new \ReflectionClass(SubmissionRepository::class);
        $defaults = $reflection->getDefaultProperties();

        // SubmissionRepository declares no custom $defaultOrderings — ordering is
        // applied at query time inside findBySurvey() and findBySurveyAndSessionHash().
        // The inherited property from Repository is an empty array.
        self::assertSame([], $defaults['defaultOrderings'] ?? []);
    }
}
