<?php

namespace Zwo3\NewsletterSubscribe\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FillsalutationCommand extends Command
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

    protected function prepareSalutations(): array
    {
        $salutations = [];
        
        $tsSettings = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
        if(isset($tsSettings['plugin.']['tx_newslettersubscribe.']['settings.']['salutation.']['languages.'])) {
            $languageConfig = $tsSettings['plugin.']['tx_newslettersubscribe.']['settings.']['salutation.']['languages.'];
            if(is_array($languageConfig) && count($languageConfig)) {
                foreach($languageConfig as $l => $lId) {
                    if(isset($tsSettings['plugin.']['tx_newslettersubscribe.']['settings.']['salutation.'][$l.'.'])) {
                        $salutations[$lId] = $tsSettings['plugin.']['tx_newslettersubscribe.']['settings.']['salutation.'][$l.'.'];
                    }
                }
            }
        }
        
        return $salutations;
    }
    
    /**
     * Executes the command to fill salutation field
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $salutations = $this->prepareSalutations();

        $counter = 0;
        $table = 'tt_address';
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());
        
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder
            ->select('uid', 'pid', 'last_name', 'title', 'gender', 'sys_language_uid')
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT))  
             )
              ->andWhere(
                  $queryBuilder->expr()->orX(
                      $queryBuilder->expr()->eq('salutation', '\'\''),
                      $queryBuilder->expr()->isNull('salutation')
                  )
              )
             //->setMaxResults(10)
             ->orderBy('uid', 'asc');
        //$io->writeln($queryBuilder->getSQL());
        $rowIterator = $queryBuilder->execute();
        
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        
        while ($row = $rowIterator->fetch()) {
            if(isset($salutations[$row['sys_language_uid']])) {
                $salutation = $salutations[$row['sys_language_uid']]['default'];
                if(isset($salutations[$row['sys_language_uid']][$row['gender']]) && !empty($row['last_name'])) {
                    $salutation = $salutations[$row['sys_language_uid']][$row['gender']];
                    $salutation .= !empty($row['title']) ? ' '.$row['title'] : '';
                    $salutation .= ' '.$row['last_name'];
                }
                //$io->writeln($salutation);
                $queryBuilder
                    ->update($table)
                    ->where(
                        $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($row['uid'], \PDO::PARAM_INT))
                    )
                    ->set('salutation', $salutation)
                    ->execute();
                $counter++;
            }
        }
        
        $io->writeln('Changed: '.$counter);
        return 0;
        //return Command::SUCCESS;
    }
}