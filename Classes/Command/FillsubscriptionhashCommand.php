<?php

namespace Zwo3\NewsletterSubscribe\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FillsubscriptionhashCommand extends Command
{   
    /**
     * @var ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * Injects the Configuration Manager and is initializing the framework settings
     *
     * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager Instance of the Configuration Manager
     */
    public function injectConfigurationManager(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager) {
        $this->configurationManager = $configurationManager;
    }

    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure()
    {

    }
    
    /**
     * Executes the command to fill subscription hash field
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
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
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT))  
             )
              ->andWhere(
                  $queryBuilder->expr()->orX(
                      $queryBuilder->expr()->eq('subscription_hash', '\'\''),
                      $queryBuilder->expr()->isNull('subscription_hash')
                  )
              )
             ->orderBy('uid', 'asc');
        $rowIterator = $queryBuilder->execute();
        
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        
        while ($row = $rowIterator->fetch()) {
            $subscriptionHash = hash('sha256', $row['email'] . $row['crdate'] ?: $row['tstamp'] . random_bytes(32));
            $queryBuilder
                ->update($table)
                ->where(
                    $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($row['uid'], \PDO::PARAM_INT))
                )
                ->set('subscription_hash', $subscriptionHash)
                ->execute();
            $counter++;
        }
        
        $io->writeln('Changed: '.$counter);
        return 0;
    }
}
