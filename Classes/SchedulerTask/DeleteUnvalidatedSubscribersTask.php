<?php

namespace Zwo3\NewsletterSubscribe\SchedulerTask;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\Task\AbstractTask;
use Zwo3\NewsletterSubscribe\Utility\DeleteUnvalidatedSubscribers;

class DeleteUnvalidatedSubscribersTask extends AbstractTask
{
    /** @var int  */
    public $days;
    
    public function execute()
    {
        $businessLogic = GeneralUtility::makeInstance(DeleteUnvalidatedSubscribers::class);
        $businessLogic->run($this->days, $this->pids);

        return true;
    }
}