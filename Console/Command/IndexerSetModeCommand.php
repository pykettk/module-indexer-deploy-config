<?php

declare(strict_types=1);

namespace Element119\IndexerDeployConfig\Console\Command;

use Element119\IndexerDeployConfig\Model\IndexerConfig;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Indexer\Console\Command\IndexerSetModeCommand as CoreIndexerSetModeCommand;
use Magento\Indexer\Model\Indexer\CollectionFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class IndexerSetModeCommand extends CoreIndexerSetModeCommand
{
    const INPUT_KEY_LOCK = 'lock';

    private Writer $configWriter;

    private IndexerConfig $indexerConfig;

    /**
     * Constructor.
     *
     * @param ObjectManagerFactory   $objectManagerFactory
     * @param Writer                 $configWriter
     * @param IndexerConfig          $indexerConfig
     * @param CollectionFactory|null $collectionFactory
     */
    public function __construct(
        ObjectManagerFactory $objectManagerFactory,
        Writer $configWriter,
        IndexerConfig $indexerConfig,
        CollectionFactory $collectionFactory = null
    ) {
        parent::__construct($objectManagerFactory, $collectionFactory);
        $this->configWriter = $configWriter;
        $this->indexerConfig = $indexerConfig;
    }

    /**
     * @return array
     */
    public function getInputList(): array
    {
        $modeOptions[] = new InputOption(
            self::INPUT_KEY_LOCK,
            'l',
            InputOption::VALUE_NONE,
            'Lock the indexer(s) in the config file'
        );
        return array_merge($modeOptions, parent::getInputList());
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $result = parent::execute($input, $output);

        $indexers = $this->getIndexers($input);
        $indexerConfig = $this->indexerConfig->getIndexerConfig();
        $mode = $input->getArgument(self::INPUT_KEY_MODE);

        if (!isset($indexerConfig['realtime'])) {
            $indexerConfig['realtime'] = [];
        }
        if (!isset($indexerConfig['schedule'])) {
            $indexerConfig['schedule'] = [];
        }

        foreach ($indexers as $indexer) {
            if (($key = array_search($indexer->getId(), $indexerConfig['realtime'], true)) !== false) {
                unset($indexerConfig['realtime'][$key]);
            }
            if (($key = array_search($indexer->getId(), $indexerConfig['schedule'], true)) !== false) {
                unset($indexerConfig['schedule'][$key]);
            }
            $indexerConfig[$mode][] = $indexer->getId();
        }

        $indexerConfig['realtime'] = array_values($indexerConfig['realtime']);
        $indexerConfig['schedule'] = array_values($indexerConfig['schedule']);

        $this->configWriter->saveConfig([ConfigFilePool::APP_CONFIG => ['indexers' => $indexerConfig]], true);

        return $result;
    }
}
