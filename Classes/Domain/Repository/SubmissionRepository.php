<?php

declare(strict_types=1);

namespace Maispace\MaiSurvey\Domain\Repository;

use Maispace\MaiSurvey\Domain\Model\Submission;
use Maispace\MaiSurvey\Domain\Model\Survey;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

class SubmissionRepository extends Repository
{
    public function findBySurveyAndSessionHash(Survey $survey, string $hash): ?Submission
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('survey', $survey),
                $query->equals('sessionHash', $hash),
            ),
        );
        $query->setLimit(1);

        /** @var Submission|null $submission */
        $submission = $query->execute()->getFirst();

        return $submission;
    }

    public function findBySurvey(Survey $survey): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching($query->equals('survey', $survey));

        return $query->execute();
    }

    /**
     * Fetches a page of submissions for a survey using LIMIT/OFFSET so callers
     * can iterate in chunks without loading the entire result set into memory.
     *
     * @return Submission[]
     */
    public function findBySurveyChunked(Survey $survey, int $limit, int $offset): array
    {
        $query = $this->createQuery();
        $query->matching($query->equals('survey', $survey));
        $query->setLimit($limit);
        $query->setOffset($offset);

        /** @var Submission[] $result */
        $result = $query->execute()->toArray();

        return $result;
    }
}
