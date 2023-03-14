<?php
/**
 * Copyright © element119. All rights reserved.
 * See LICENCE.txt for licence details.
 */
declare(strict_types=1);

namespace Element119\IndexerDeployConfig\Console\Command;

use InvalidArgumentException;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Console\Cli;
use Magento\Indexer\Console\Command\AbstractIndexerCommand;
use Magento\Indexer\Console\Command\IndexerSetModeCommand;
use Magento\Indexer\Model\Indexer;
use Magento\Indexer\Model\Indexer\CollectionFactory;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class IndexerLockAll extends AbstractIndexerCommand
{
    /** @var Writer */
    private Writer $configWriter;

    /** @var array */
    public array $modes = [
        IndexerSetModeCommand::INPUT_KEY_SCHEDULE,
        IndexerSetModeCommand::INPUT_KEY_REALTIME
    ];

    /**
     * @param ObjectManagerFactory $objectManagerFactory
     * @param Writer $configWriter
     * @param CollectionFactory|null $collectionFactory
     */
    public function __construct(
        ObjectManagerFactory $objectManagerFactory,
        Writer $configWriter,
        CollectionFactory $collectionFactory = null
    ) {
        parent::__construct($objectManagerFactory, $collectionFactory);
        $this->configWriter = $configWriter;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $modeInput = $input->getOption(IndexerSetModeCommand::INPUT_KEY_MODE);

        if (!is_null($modeInput) && !in_array($modeInput, $this->modes, true)) {
            throw new InvalidArgumentException(
                'Passed mode must be one of: ' . implode(', ', $this->modes)
            );
        }

        $indexerConfig = [];

        /** @var Indexer $indexer */
        foreach ($this->getAllIndexers() as $indexer) {
            $mode = $modeInput;
            
            if (is_null($modeInput)) {
                $mode = $indexer->isScheduled()
                    ? IndexerSetModeCommand::INPUT_KEY_SCHEDULE
                    : IndexerSetModeCommand::INPUT_KEY_REALTIME;
            }
            
            $indexerConfig[$mode][] = $indexer->getIndexerId();
            
            $output->writeln(sprintf(
                '%s indexer has been locked to %s',
                $indexer->getTitle(),
                $mode === IndexerSetModeCommand::INPUT_KEY_SCHEDULE ? 'Update on Schedule' : 'Update on Save'
            ));
        }

        $this->configWriter->saveConfig([ConfigFilePool::APP_CONFIG => ['indexers' => $indexerConfig]], true);
        $output->writeln("\nIndexers locked. Please run app:config:import.");
        
        return Cli::RETURN_SUCCESS;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('indexer:lock-all');
        $this->setDescription('Lock all indexers');
        $this->setDefinition([
            new InputOption(
                IndexerSetModeCommand::INPUT_KEY_MODE,
                'm',
                InputArgument::OPTIONAL,
                sprintf(
                    'Passing one of two modes (%s) will lock all indexers to that mode.',
                    implode(', ', $this->modes)
                ),
                null
            ),
        ]);
        
        parent::configure();
    }
}
