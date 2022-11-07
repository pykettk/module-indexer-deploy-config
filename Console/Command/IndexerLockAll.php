<?php

declare(strict_types=1);

namespace Element119\IndexerDeployConfig\Console\Command;

use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Indexer\Console\Command\AbstractIndexerCommand;
use Magento\Indexer\Model\Indexer\CollectionFactory;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class IndexerLockAll extends AbstractIndexerCommand
{
    private \Magento\Framework\App\DeploymentConfig\Writer $configWriter;

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

    public const MODE_OPTION = 'mode';

    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $mode = $input->getOption(self::MODE_OPTION);

        $indexerConfig = [];

        foreach ($this->getAllIndexers() as $indexer) {
            $indexerConfig[$mode][] = $indexer->getIndexerId();
        }

        $this->configWriter->saveConfig([ConfigFilePool::APP_CONFIG => ['indexers' => $indexerConfig]], true);

        $output->writeln('All indexers have been locked to ' . ($mode === 'schedule' ? 'Update on Schedule' : 'Update on Save'));
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('indexer:lock-all');
        $this->setDescription('Lock all indexers (default locks to Update on Schedule)');
        $this->setDefinition([
            new InputOption(self::MODE_OPTION, 'm', InputArgument::OPTIONAL, 'Mode', 'schedule'),
        ]);
        parent::configure();
    }
}
