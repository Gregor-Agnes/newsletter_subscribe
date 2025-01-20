<?php
declare(strict_types=1);

namespace Zwo3\NewsletterSubscribe\Utility;


use TYPO3\CMS\Core\Utility\GeneralUtility;
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
    
    public function run($days, $pids)
    {
        if ((int)$days < 7) {
            $days = 7;
        }
        
        $this->subscriptionRepository = GeneralUtility::makeInstance(SubscriptionRepository::class);
        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);
        $query = $this->subscriptionRepository->createQuery();
        $querySettings = $query->getQuerySettings();
        $querySettings->setRespectStoragePage(false);
        $querySettings->setIgnoreEnableFields(true);
        $this->subscriptionRepository->setDefaultQuerySettings($querySettings);
        
        $addresses = $this->subscriptionRepository->findOldUnvalidated((int) $days, (string) $pids);
        
        foreach ($addresses as $address) {
            $this->subscriptionRepository->remove($address);
        }
        
        $this->persistenceManager->persistAll();
        
    }
}