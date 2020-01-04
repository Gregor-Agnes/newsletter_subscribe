<?php

namespace Zwo3\Subscribe\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Class SubscriptionRepository
 *
 * @package Zwo3\Subscribe\Domain\Repository
 */
class SubscriptionRepository extends Repository {

    public function initializeObject() {

        $query = $this->createQuery();
        $querySettings = $query->getQuerySettings();
        $querySettings->setIgnoreEnableFields(true);
        $this->setDefaultQuerySettings($querySettings);
    }


}