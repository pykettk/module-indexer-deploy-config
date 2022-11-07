<?php

declare(strict_types=1);

namespace Element119\IndexerDeployConfig\Console\Command;

use InvalidArgumentException;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\Config\File\ConfigFilePool;
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
    private \Magento\Framework\App\DeploymentConfig\Writer $configWriter;

    public array $modes = [
        IndexerSetModeCommand::INPUT_KEY_SCHEDULE,
        IndexerSetModeCommand::INPUT_KEY_REALTIME
    ];

    /**
     * Constructor.
     *
     * @param ObjectManagerFactory   $objectManagerFactory
     * @param Writer                 $configWriter
     * @param CollectionFactory|null $collectionFactory
     */
    public function __construct(
        ObjectManagerFactory $objectManagerFactory,
        \Magento\Framework\App\DeploymentConfig\Writer $configWriter,
        \Magento\Indexer\Model\Indexer\CollectionFactory $collectionFactory = null
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
                $mode = $indexer->isScheduled() ? IndexerSetModeCommand::INPUT_KEY_SCHEDULE : IndexerSetModeCommand::INPUT_KEY_REALTIME;
            }
            $indexerConfig[$mode][] = $indexer->getIndexerId();
            $output->writeln($indexer->getTitle() . ' indexer has been locked to ' . ($mode === IndexerSetModeCommand::INPUT_KEY_SCHEDULE ? 'Update on Schedule' : 'Update on Save'));
        }

        $this->configWriter->saveConfig([ConfigFilePool::APP_CONFIG => ['indexers' => $indexerConfig]], true);
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
                IndexerSetModeCommand::INPUT_KEY_MODE, 'm',
                InputArgument::OPTIONAL,
                'Passing one of two modes (' . implode(', ',
                    $this->modes) . ') will lock all indexers to that mode',
                null
            ),
        ]);
        parent::configure();
    }
}
