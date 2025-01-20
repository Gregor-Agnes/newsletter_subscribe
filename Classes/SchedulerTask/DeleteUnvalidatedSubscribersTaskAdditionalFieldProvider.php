<?php
declare(strict_types=1);

namespace Zwo3\NewsletterSubscribe\SchedulerTask;

use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Scheduler\AbstractAdditionalFieldProvider;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Scheduler\Task\AbstractTask;
use TYPO3\CMS\Scheduler\Task\Enumeration\Action;

/**
 * Class DeleteUnvalidatedSubscribersTaskAdditionalFieldProvider
 *
 * @package Zwo3\NewsletterSubscribe\SchedulerTask
 */
class DeleteUnvalidatedSubscribersTaskAdditionalFieldProvider extends AbstractAdditionalFieldProvider
{
    
    /**
     * This method is used to define new fields for adding or editing a task
     * In this case, it adds an email field
     *
     * @param array $taskInfo Reference to the array containing the info used in the add/edit form
     * @param AbstractTask|null $task When editing, reference to the current task. NULL when adding.
     * @param SchedulerModuleController $schedulerModule
     * @return array Array containing all the information pertaining to the additional fields
     */
    public function getAdditionalFields(
        array &$taskInfo,
        $task,
        SchedulerModuleController $schedulerModule
    ): array {
        $currentSchedulerModuleAction = $schedulerModule->getCurrentAction();
        
        $additionalFields = [];
        // Initialize extra field value
        if (empty($taskInfo['days'])) {
            if ($currentSchedulerModuleAction->name === 'ADD') {
                // In case of new task and if field is empty, set default days address
                $taskInfo['days'] = 60;
            } elseif ($currentSchedulerModuleAction->name === 'EDIT') {
                // In case of edit, and editing a test task, set to internal value if not data was submitted already
                $taskInfo['days'] = $task->days;
            } else {
                // Otherwise set an empty value, as it will not be used anyway
                $taskInfo['days'] = '';
            }
        }
        // Write the code for the field
        $fieldID = 'task_days';
        $fieldCode = '<input type="text" class="form-control" name="tx_scheduler[newsletter_subscribe][days]" id="' . $fieldID . '" value="' . htmlspecialchars((string)$taskInfo['days']) . '" size="30">';
        $additionalFields[$fieldID] = [
            'code' => $fieldCode,
            'label' => $this->getLanguageService()->sL('LLL:EXT:newsletter_subscribe/Resources/Private/Language/locallang.xlf:schedulerAge'),
            'cshKey' => '_MOD_system_txschedulerM1',
            'cshLabel' => $fieldID
        ];
        
        // only PIDs
        if (empty($taskInfo['pids'])) {
            if ($currentSchedulerModuleAction->name === 'ADD') {
                // In case of new task and if field is empty, set default pids address
                $taskInfo['pids'] = 0;
            } elseif ($currentSchedulerModuleAction->name === 'EDIT') {
                // In case of edit, and editing a test task, set to internal value if not data was submitted already
                $taskInfo['pids'] = $task->pids;
            } else {
                // Otherwise set an empty value, as it will not be used anyway
                $taskInfo['pids'] = null;
            }
        }
        // Write the code for the field
        $fieldID = 'task_pids';
        $fieldCode = '<input type="text" class="form-control" name="tx_scheduler[newsletter_subscribe][pids]" id="' . $fieldID . '" value="' . htmlspecialchars((string)$taskInfo['pids']) . '" size="30">';
        $additionalFields[$fieldID] = [
            'code' => $fieldCode,
            'label' => $this->getLanguageService()->sL('LLL:EXT:newsletter_subscribe/Resources/Private/Language/locallang.xlf:schedulerPids'),
            'cshKey' => '_MOD_system_txschedulerM1',
            'cshLabel' => $fieldID
        ];
        return $additionalFields;
    }
    
    /**
     * This method checks any additional data that is relevant to the specific task
     * If the task class is not relevant, the method is expected to return TRUE
     *
     * @param array $submittedData Reference to the array containing the data submitted by the user
     * @param SchedulerModuleController $schedulerModule Reference to the calling object (Scheduler's BE module)
     * @return bool TRUE if validation was ok (or selected class is not relevant), FALSE otherwise
     */
    public function validateAdditionalFields(
        array &$submittedData,
        SchedulerModuleController $schedulerModule
    ): bool {
        $submittedData['days'] = (int)(trim($submittedData['newsletter_subscribe']['days']));
        if (empty($submittedData['days']) || $submittedData['days'] < 7 || !is_int($submittedData['days'])) {
            $this->addMessage(
                $this->getLanguageService()->sL('LLL:EXT:newsletter_subscribe/Resources/Private/Language/locallang.xlf:error.schedulerAge'),
                ContextualFeedbackSeverity::ERROR
            );
            $dayResult = false;
        } else {
            $dayResult = true;
        }
        if (!preg_match('/^[0-9]+(,[0-9]*)*$/', $submittedData['newsletter_subscribe']['pids'])) {
            $this->addMessage(
                $this->getLanguageService()->sL('LLL:EXT:newsletter_subscribe/Resources/Private/Language/locallang.xlf:error.schedulerPids'),
                ContextualFeedbackSeverity::ERROR
            );
            $pidResult = false;
        } else {
            $pidResult = true;
        }
        
        return ($dayResult && $pidResult);
    }
    
    /**
     * This method is used to save any additional input into the current task object
     * if the task class matches
     *
     * @param array $submittedData Array containing the data submitted by the user
     * @param AbstractTask $task Reference to the current task object
     */
    public function saveAdditionalFields(array $submittedData, AbstractTask $task): void
    {
        $task->days = (int) $submittedData['newsletter_subscribe']['days'];
        $task->pids = (int) $submittedData['newsletter_subscribe']['pids'];
    }
    
    /**
     * @return LanguageService|null
     */
    protected function getLanguageService(): ?LanguageService
    {
        return $GLOBALS['LANG'] ?? null;
    }
}