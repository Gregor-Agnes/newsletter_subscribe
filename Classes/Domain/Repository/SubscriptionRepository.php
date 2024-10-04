<?php
declare(strict_types=1);

namespace Zwo3\NewsletterSubscribe\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

/**
 * Class SubscriptionRepository
 *
 * @package Zwo3\NewsletterSubscribe\Domain\Repository
 */
class SubscriptionRepository extends Repository {
    
    public function findSubscriptionByUid(int $uid, bool $respectEnableFields = true)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);
        $query->getQuerySettings()->setRespectSysLanguage(false);
        $query->getQuerySettings()->setIgnoreEnableFields(!$respectEnableFields);
        
        return $query->matching(
            $query->logicalAnd(
                $query->equals('uid', $uid),
                $query->equals('deleted', 0)
            ))->execute()->getFirst();
    }
    
    public function findOneByEmail(string $email, bool $respectEnableFields = true)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectSysLanguage(false);
        $query->getQuerySettings()->setIgnoreEnableFields(!$respectEnableFields);
        
        return $query->matching(
            $query->logicalAnd(
                $query->equals('email', $email),
                $query->equals('deleted', 0)
            ))->execute()->getFirst();
    }
    
    public function findOldUnvalidated(int $days, string $pids)
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->lessThan('crdate', time() - 86400 * $days),
                $query->equals('hidden', true),
                $query->in('pid', explode(',', $pids)),
            )
        );
        $query->setLimit(10);
        
        return $query->execute();
    }
}