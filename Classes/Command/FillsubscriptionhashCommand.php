<?php
declare(strict_types=1);

namespace Zwo3\NewsletterSubscribe\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FillsubscriptionhashCommand extends Command
{
    /**
     * Executes the command to fill subscription hash field
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int error code
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $counter = 0;
        $table = 'tt_address';
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());
        
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder
            ->select('uid', 'pid', 'email', 'crdate', 'tstamp')
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT))
            )
            ->andWhere(
                $queryBuilder->expr()->or(
                    $queryBuilder->expr()->eq('subscription_hash', '\'\''),
                    $queryBuilder->expr()->isNull('subscription_hash')
                )
            )
            ->orderBy('uid', 'asc');
        $rowIterator = $queryBuilder->executeQuery();
        
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        
        while ($row = $rowIterator->fetch()) {
            $subscriptionHash = hash('sha256', $row['email'] . $row['crdate'] ?: $row['tstamp'] . random_bytes(32));
            $queryBuilder
                ->update($table)
                ->where(
                    $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($row['uid'], Connection::PARAM_INT))
                )
                ->set('subscription_hash', $subscriptionHash)
                ->executeQuery();
            $counter++;
        }
        
        $io->writeln('Changed: '.$counter);
        return Command::SUCCESS;
    }
}