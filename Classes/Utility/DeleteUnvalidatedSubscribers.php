<?php

namespace Zwo3\NewsletterSubscribe\Utility;


use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

use Zwo3\NewsletterSubscribe\Domain\Repository\SubscriptionRepository;
use function Sodium\add;

class DeleteUnvalidatedSubscribers
{
    
    /**
     * @var AddressRepository
     */
    public $subscriptionRepository;

    /**
     * @var PersistenceManager
     */
    public $persistenceManager;
    
    /**
     * @var ObjectManager
     */
    public $objectManager;
    
    public function run($days, $pids)
    {
        if ((int)$days < 7) {
            $days = 7;
        }

        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->subscriptionRepository = $this->objectManager->get(SubscriptionRepository::class);
        $this->persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $query = $this->subscriptionRepository->createQuery();
        $querySettings = $query->getQuerySettings();
        $querySettings->setRespectStoragePage(false);
        $querySettings->setIgnoreEnableFields(true);
        $this->subscriptionRepository->setDefaultQuerySettings($querySettings);
        
        $addresses = $this->subscriptionRepository->findOldUnvalidated($days, $pids);

        foreach ($addresses as $address) {
            $this->subscriptionRepository->remove($address);
        }

        $this->persistenceManager->persistAll();
     
    }
}