<?php

declare(strict_types=1);

namespace Maispace\MaiSurvey\Service;

use Maispace\MaiSurvey\Domain\Model\Answer;
use Maispace\MaiSurvey\Domain\Model\Question;
use Maispace\MaiSurvey\Domain\Model\Submission;
use Maispace\MaiSurvey\Domain\Model\Survey;
use Maispace\MaiSurvey\Domain\Repository\SubmissionRepository;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

class SubmissionService
{
    public function __construct(
        private readonly SubmissionRepository $submissionRepository,
        private readonly PersistenceManager $persistenceManager,
        private readonly Context $context,
    ) {}

    public function generateSessionHash(Survey $survey, ServerRequestInterface $request): string
    {
        $cookieName = 'mai_survey_' . $survey->getUid();
        $cookies = $request->getCookieParams();

        if (!empty($cookies[$cookieName])) {
            return hash('sha256', $survey->getUid() . '|' . $cookies[$cookieName]);
        }

        $sessionCookie = $cookies['fe_typo_user'] ?? '';
        if ($sessionCookie !== '') {
            return hash('sha256', $survey->getUid() . '|' . $sessionCookie);
        }

        return hash('sha256', $survey->getUid() . '|' . GeneralUtility::makeInstance(\TYPO3\CMS\Core\Crypto\Random::class)->generateRandomHexString(32));
    }

    public function isDuplicate(Survey $survey, string $sessionHash): bool
    {
        return $this->submissionRepository->findBySurveyAndSessionHash($survey, $sessionHash) !== null;
    }

    /**
     * @param array<string, mixed> $answers
     * @param Question[]           $questions
     */
    public function persist(Survey $survey, array $answers, array $questions, string $sessionHash): Submission
    {
        $submission = new Submission();
        $submission->setSurvey($survey);
        $submission->setSessionHash($sessionHash);
        $submission->setSubmittedAt(new \DateTime());

        try {
            $feUserUid = (int) $this->context->getPropertyFromAspect('frontend.user', 'id');
        } catch (\Throwable) {
            $feUserUid = 0;
        }

        $submission->setFeUserUid($feUserUid);

        foreach ($questions as $question) {
            $uid = (string) $question->getUid();
            if (!isset($answers[$uid])) {
                continue;
            }

            $rawValue = $answers[$uid];
            $value = is_array($rawValue) ? implode(',', $rawValue) : (string) $rawValue;

            $answer = new Answer();
            $answer->setQuestion($question);
            $answer->setValue($value);
            $answer->setSubmission($submission);
            $submission->addAnswer($answer);
        }

        $this->submissionRepository->add($submission);
        $this->persistenceManager->persistAll();

        return $submission;
    }
}
