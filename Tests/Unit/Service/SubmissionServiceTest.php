<?php

declare(strict_types=1);

namespace Maispace\MaiSurvey\Tests\Unit\Service;

use Maispace\MaiSurvey\Domain\Model\Question;
use Maispace\MaiSurvey\Domain\Model\Submission;
use Maispace\MaiSurvey\Domain\Model\Survey;
use Maispace\MaiSurvey\Domain\Repository\SubmissionRepository;
use Maispace\MaiSurvey\Service\SubmissionService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

final class SubmissionServiceTest extends TestCase
{
    private SubmissionRepository&MockObject $submissionRepository;
    private PersistenceManager&MockObject $persistenceManager;
    private Context&MockObject $context;
    private SubmissionService $subject;

    protected function setUp(): void
    {
        $this->submissionRepository = $this->createMock(SubmissionRepository::class);
        $this->persistenceManager = $this->createMock(PersistenceManager::class);
        $this->context = $this->createMock(Context::class);

        $this->subject = new SubmissionService(
            $this->submissionRepository,
            $this->persistenceManager,
            $this->context,
        );
    }

    // ── generateSessionHash ───────────────────────────────────────────────────

    #[Test]
    public function generateSessionHashUsesSurveyCookieWhenPresent(): void
    {
        $survey = new Survey();
        $survey->_setProperty('uid', 42);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getCookieParams')->willReturn([
            'mai_survey_42' => 'my-session-token',
        ]);

        $hash = $this->subject->generateSessionHash($survey, $request);

        $expected = hash('sha256', '42|my-session-token');
        self::assertSame($expected, $hash);
    }

    #[Test]
    public function generateSessionHashUsesFESessionCookieAsFallback(): void
    {
        $survey = new Survey();
        $survey->_setProperty('uid', 7);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getCookieParams')->willReturn([
            'fe_typo_user' => 'fe-session-xyz',
        ]);

        $hash = $this->subject->generateSessionHash($survey, $request);

        $expected = hash('sha256', '7|fe-session-xyz');
        self::assertSame($expected, $hash);
    }

    #[Test]
    public function generateSessionHashReturnsSha256String(): void
    {
        $survey = new Survey();
        $survey->_setProperty('uid', 1);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getCookieParams')->willReturn([
            'mai_survey_1' => 'token',
        ]);

        $hash = $this->subject->generateSessionHash($survey, $request);

        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $hash);
    }

    // ── isDuplicate ───────────────────────────────────────────────────────────

    #[Test]
    public function isDuplicateReturnsTrueWhenSubmissionExists(): void
    {
        $survey = new Survey();
        $existingSubmission = new Submission();

        $this->submissionRepository
            ->method('findBySurveyAndSessionHash')
            ->with($survey, 'known-hash')
            ->willReturn($existingSubmission);

        self::assertTrue($this->subject->isDuplicate($survey, 'known-hash'));
    }

    #[Test]
    public function isDuplicateReturnsFalseWhenNoSubmissionExists(): void
    {
        $survey = new Survey();

        $this->submissionRepository
            ->method('findBySurveyAndSessionHash')
            ->willReturn(null);

        self::assertFalse($this->subject->isDuplicate($survey, 'unknown-hash'));
    }

    // ── persist ───────────────────────────────────────────────────────────────

    #[Test]
    public function persistReturnsSubmissionInstance(): void
    {
        $this->context->method('getPropertyFromAspect')->willReturn(0);
        $this->submissionRepository->method('add');
        $this->persistenceManager->method('persistAll');

        $survey = new Survey();
        $result = $this->subject->persist($survey, [], [], 'hash-abc');

        self::assertInstanceOf(Submission::class, $result);
    }

    #[Test]
    public function persistSetsSurveyOnSubmission(): void
    {
        $this->context->method('getPropertyFromAspect')->willReturn(0);
        $this->submissionRepository->method('add');
        $this->persistenceManager->method('persistAll');

        $survey = new Survey();
        $submission = $this->subject->persist($survey, [], [], 'hash-abc');

        self::assertSame($survey, $submission->getSurvey());
    }

    #[Test]
    public function persistSetsSessionHashOnSubmission(): void
    {
        $this->context->method('getPropertyFromAspect')->willReturn(0);
        $this->submissionRepository->method('add');
        $this->persistenceManager->method('persistAll');

        $survey = new Survey();
        $submission = $this->subject->persist($survey, [], [], 'session-hash-xyz');

        self::assertSame('session-hash-xyz', $submission->getSessionHash());
    }

    #[Test]
    public function persistSetsSubmittedAtToCurrentTime(): void
    {
        $this->context->method('getPropertyFromAspect')->willReturn(0);
        $this->submissionRepository->method('add');
        $this->persistenceManager->method('persistAll');

        $before = new \DateTime();
        $survey = new Survey();
        $submission = $this->subject->persist($survey, [], [], 'hash');
        $after = new \DateTime();

        self::assertNotNull($submission->getSubmittedAt());
        self::assertGreaterThanOrEqual($before, $submission->getSubmittedAt());
        self::assertLessThanOrEqual($after, $submission->getSubmittedAt());
    }

    #[Test]
    public function persistSetsFeUserUidFromContext(): void
    {
        $this->context->method('getPropertyFromAspect')
            ->with('frontend.user', 'id')
            ->willReturn(99);
        $this->submissionRepository->method('add');
        $this->persistenceManager->method('persistAll');

        $survey = new Survey();
        $submission = $this->subject->persist($survey, [], [], 'hash');

        self::assertSame(99, $submission->getFeUserUid());
    }

    #[Test]
    public function persistFallsBackToFeUserUidZeroOnContextException(): void
    {
        $this->context->method('getPropertyFromAspect')
            ->willThrowException(new \RuntimeException('Context not available'));
        $this->submissionRepository->method('add');
        $this->persistenceManager->method('persistAll');

        $survey = new Survey();
        $submission = $this->subject->persist($survey, [], [], 'hash');

        self::assertSame(0, $submission->getFeUserUid());
    }

    #[Test]
    public function persistCreatesAnswerForEachMatchedQuestion(): void
    {
        $this->context->method('getPropertyFromAspect')->willReturn(0);

        $capturedSubmission = null;
        $this->submissionRepository->method('add')->willReturnCallback(
            static function (Submission $s) use (&$capturedSubmission): void {
                $capturedSubmission = $s;
            },
        );
        $this->persistenceManager->method('persistAll');

        $q1 = new Question();
        $q1->_setProperty('uid', 1);
        $q2 = new Question();
        $q2->_setProperty('uid', 2);

        $survey = new Survey();
        $submission = $this->subject->persist($survey, ['1' => 'Yes', '2' => 'No'], [$q1, $q2], 'hash');

        self::assertCount(2, $submission->getAnswers());
    }

    #[Test]
    public function persistSkipsQuestionsWithoutMatchingAnswer(): void
    {
        $this->context->method('getPropertyFromAspect')->willReturn(0);
        $this->submissionRepository->method('add');
        $this->persistenceManager->method('persistAll');

        $q1 = new Question();
        $q1->_setProperty('uid', 1);
        $q2 = new Question();
        $q2->_setProperty('uid', 2);

        $survey = new Survey();
        // Only answer for question 1; question 2 has no response.
        $submission = $this->subject->persist($survey, ['1' => 'Maybe'], [$q1, $q2], 'hash');

        self::assertCount(1, $submission->getAnswers());
    }

    #[Test]
    public function persistJoinsArrayAnswersWithComma(): void
    {
        $this->context->method('getPropertyFromAspect')->willReturn(0);
        $this->submissionRepository->method('add');
        $this->persistenceManager->method('persistAll');

        $q1 = new Question();
        $q1->_setProperty('uid', 1);

        $survey = new Survey();
        $submission = $this->subject->persist($survey, ['1' => ['Option A', 'Option B']], [$q1], 'hash');

        $answers = array_values(iterator_to_array($submission->getAnswers()));
        self::assertCount(1, $answers);
        self::assertSame('Option A,Option B', $answers[0]->getValue());
    }

    #[Test]
    public function persistCallsRepositoryAddAndPersistAll(): void
    {
        $this->context->method('getPropertyFromAspect')->willReturn(0);
        $this->submissionRepository->expects(self::once())->method('add');
        $this->persistenceManager->expects(self::once())->method('persistAll');

        $survey = new Survey();
        $this->subject->persist($survey, [], [], 'hash');
    }
}
