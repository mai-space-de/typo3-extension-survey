<?php

declare(strict_types=1);

namespace Maispace\MaiSurvey\Controller;

use Maispace\MaiBase\Controller\AbstractActionController;
use Maispace\MaiBase\Controller\Traits\AppendDataToPluginVariablesTrait;
use Maispace\MaiBase\Controller\Traits\FlashMessageTrait;
use Maispace\MaiBase\Controller\Traits\PageRendererTrait;
use Maispace\MaiSurvey\Domain\Model\Question;
use Maispace\MaiSurvey\Domain\Model\Survey;
use Maispace\MaiSurvey\Domain\Repository\SurveyRepository;
use Maispace\MaiSurvey\Service\SubmissionService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Page\PageRenderer;

class SurveyController extends AbstractActionController
{
    use AppendDataToPluginVariablesTrait;
    use PageRendererTrait;
    use FlashMessageTrait;

    public function __construct(
        private readonly SurveyRepository $surveyRepository,
        private readonly SubmissionService $submissionService,
    ) {}

    public function injectPageRenderer(PageRenderer $pageRenderer): void
    {
        $this->pageRenderer = $pageRenderer;
    }

    public function injectAssetCollector(AssetCollector $assetCollector): void
    {
        $this->assetCollector = $assetCollector;
    }

    public function listAction(): ResponseInterface
    {
        $this->registerAssets();
        $this->view->assign('surveys', $this->surveyRepository->findActive());

        return $this->htmlResponse();
    }

    public function showAction(Survey $survey): ResponseInterface
    {
        if (!$survey->isActive()) {
            $this->flashError('survey.error.inactive');
        }

        $this->registerAssets();
        $this->view->assignMultiple([
            'survey' => $survey,
            'questions' => $this->getQuestionsArray($survey),
        ]);

        return $this->htmlResponse();
    }

    public function stepAction(Survey $survey, int $step = 1): ResponseInterface
    {
        if (!$survey->isActive()) {
            $this->flashError('survey.error.inactive');

            return $this->redirect('list');
        }

        $questions = $this->getQuestionsArray($survey);
        $totalSteps = max(1, count($questions));
        $currentStep = max(1, min($step, $totalSteps));

        $this->registerAssets();
        $this->view->assignMultiple([
            'survey' => $survey,
            'questions' => $questions,
            'currentQuestion' => $questions[$currentStep - 1] ?? null,
            'currentStep' => $currentStep,
            'totalSteps' => $totalSteps,
            'progressPercentage' => (int) round(($currentStep / $totalSteps) * 100),
        ]);

        return $this->htmlResponse();
    }

    public function submitAction(Survey $survey): ResponseInterface
    {
        if (!$survey->isActive()) {
            $this->flashError('survey.error.inactive');

            return $this->redirect('list');
        }

        $sessionHash = $this->submissionService->generateSessionHash($survey, $this->request);

        if ($survey->isPreventDuplicates() && $this->submissionService->isDuplicate($survey, $sessionHash)) {
            $this->flashError('survey.error.duplicate');

            return $this->redirect('confirmation');
        }

        $rawAnswers = $this->request->hasArgument('answers')
            ? (array) $this->request->getArgument('answers')
            : [];

        $this->submissionService->persist($survey, $rawAnswers, $this->getQuestionsArray($survey), $sessionHash);

        $this->flashSuccess('survey.confirmation.message');

        return $this->redirect('confirmation');
    }

    public function confirmationAction(): ResponseInterface
    {
        $this->registerAssets();

        return $this->htmlResponse();
    }

    /**
     * @return array<int, Question>
     */
    private function getQuestionsArray(Survey $survey): array
    {
        $questions = [];
        foreach ($survey->getQuestions() as $question) {
            $questions[] = $question;
        }

        return $questions;
    }

    private function registerAssets(): void
    {
        $this->addCssFile('mai_survey_css', 'EXT:mai_survey/Resources/Public/Css/survey.css');
        $this->addJsFile('mai_survey_js', 'EXT:mai_survey/Resources/Public/JavaScript/survey.js');
    }
}
