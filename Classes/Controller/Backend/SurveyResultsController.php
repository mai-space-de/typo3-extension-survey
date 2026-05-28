<?php

declare(strict_types=1);

namespace Maispace\MaiSurvey\Controller\Backend;

use Maispace\MaiBase\Controller\Backend\AbstractBackendController;
use Maispace\MaiBase\Controller\Backend\Traits\BackendCsvExportTrait;
use Maispace\MaiBase\Controller\Traits\FlashMessageTrait;
use Maispace\MaiBase\Controller\Traits\PaginationTrait;
use Maispace\MaiSurvey\Domain\Model\Survey;
use Maispace\MaiSurvey\Domain\Repository\SubmissionRepository;
use Maispace\MaiSurvey\Domain\Repository\SurveyRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Imaging\IconFactory;

#[AsController]
class SurveyResultsController extends AbstractBackendController
{
    use BackendCsvExportTrait;
    use FlashMessageTrait;
    use PaginationTrait;

    public function __construct(
        ModuleTemplateFactory $moduleTemplateFactory,
        IconFactory $iconFactory,
        private readonly SurveyRepository $surveyRepository,
        private readonly SubmissionRepository $submissionRepository,
    ) {
        parent::__construct($moduleTemplateFactory, $iconFactory);
    }

    public function indexAction(): ResponseInterface
    {
        return $this->listAction();
    }

    public function listAction(): ResponseInterface
    {
        $moduleTemplate = $this->createModuleTemplate();
        $allSurveys = $this->surveyRepository->findAll();
        [$pagination, $paginator] = $this->paginateQueryResult($allSurveys);
        $this->assignMultiple($moduleTemplate, [
            'surveys' => $paginator->getPaginatedItems(),
            'pagination' => $pagination,
        ]);

        return $this->renderModuleResponse($moduleTemplate, 'List');
    }

    public function showAction(Survey $survey): ResponseInterface
    {
        $moduleTemplate = $this->createModuleTemplate();
        $allSubmissions = $this->submissionRepository->findBySurvey($survey);
        [$pagination, $paginator] = $this->paginateQueryResult($allSubmissions);
        $this->assignMultiple($moduleTemplate, [
            'survey' => $survey,
            'submissions' => $paginator->getPaginatedItems(),
            'pagination' => $pagination,
        ]);

        return $this->renderModuleResponse($moduleTemplate, 'Show');
    }

    public function exportAction(Survey $survey): ResponseInterface
    {
        $chunkSize = 250;
        $offset = 0;
        $rows = [['submission_uid', 'submitted_at', 'fe_user_uid', 'session_hash', 'question', 'value']];

        do {
            $chunk = $this->submissionRepository->findBySurveyChunked($survey, $chunkSize, $offset);

            foreach ($chunk as $submission) {
                $answers = $submission->getAnswers();
                if (count($answers) === 0) {
                    $rows[] = [
                        (string) $submission->getUid(),
                        $submission->getSubmittedAt()?->format('Y-m-d H:i:s') ?? '',
                        (string) $submission->getFeUserUid(),
                        $submission->getSessionHash(),
                        '',
                        '',
                    ];

                    continue;
                }

                foreach ($answers as $answer) {
                    $rows[] = [
                        (string) $submission->getUid(),
                        $submission->getSubmittedAt()?->format('Y-m-d H:i:s') ?? '',
                        (string) $submission->getFeUserUid(),
                        $submission->getSessionHash(),
                        $answer->getQuestion()?->getQuestion() ?? '',
                        $answer->getValue(),
                    ];
                }
            }

            $offset += $chunkSize;
        } while (count($chunk) === $chunkSize);

        return $this->csvDownloadResponse($rows, sprintf('survey-%d-results.csv', $survey->getUid()));
    }
}
