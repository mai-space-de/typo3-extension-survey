<?php

declare(strict_types=1);

namespace Maispace\MaiSurvey\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

class SurveyRepository extends Repository
{
    protected $defaultOrderings = [
        'sorting' => QueryInterface::ORDER_ASCENDING,
    ];

    public function findActive(): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching($query->equals('isActive', true));

        return $query->execute();
    }
}
