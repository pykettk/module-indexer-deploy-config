<?php
/**
 * Copyright © element119. All rights reserved.
 * See LICENCE.txt for licence details.
 */
declare(strict_types=1);

namespace Element119\IndexerDeployConfig\Cron;

use Element119\IndexerDeployConfig\Model\IndexerConfig;
use Element119\IndexerDeployConfig\Scope\ModuleConfig;
use Magento\Indexer\Console\Command\IndexerSetModeCommand as IndexerMode;
use Magento\Indexer\Model\Indexer\CollectionFactory as IndexerCollectionFactory;
use Magento\Indexer\Model\Indexer;

class SetIndexerMode
{
    /** @var IndexerConfig */
    private IndexerConfig $indexerConfig;

    /** @var ModuleConfig */
    private ModuleConfig $moduleConfig;

    /** @var IndexerCollectionFactory */
    private IndexerCollectionFactory $indexerCollectionFactory;

    /**
     * @param IndexerConfig $indexerConfig
     * @param ModuleConfig $moduleConfig
     * @param IndexerCollectionFactory $indexerCollectionFactory
     */
    public function __construct(
        IndexerConfig $indexerConfig,
        ModuleConfig $moduleConfig,
        IndexerCollectionFactory $indexerCollectionFactory
    ) {
        $this->indexerConfig = $indexerConfig;
        $this->moduleConfig = $moduleConfig;
        $this->indexerCollectionFactory = $indexerCollectionFactory;
    }

    /**
     * Ensure indexers are in the mode they are configured to be locked to.
     *
     * Can be disabled in the admin:
     * Stores -> Settings -> Configuration -> Advanced -> System -> Indexer Mode Locking -> Enable Cron Fallback -> No
     *
     * @return void
     */
    public function execute(): void
    {
        if (!$this->moduleConfig->isCronFallbackEnabled()) {
            return; // fallback disabled, nothing to do
        }

        if (!($indexerConfig = $this->indexerConfig->getFlatIndexerConfig())) {
            return; // indexers are not locked, nothing to do
        }

        /** @var Indexer[] $allIndexers */
        $allIndexers = $this->indexerCollectionFactory->create()->getItems();

        foreach ($allIndexers as $indexer) {
            foreach ($indexerConfig as $indexerId => $lockedMode) {
                if ($indexer->getId() !== $indexerId) {
                    continue; // ensure we're looking at the same indexer in both loops
                }

                $indexerMode = $indexer->isScheduled()
                    ? IndexerMode::INPUT_KEY_SCHEDULE
                    : IndexerMode::INPUT_KEY_REALTIME;

                if ($indexerMode !== $lockedMode) {
                    $indexer->setScheduled($lockedMode === IndexerMode::INPUT_KEY_SCHEDULE);
                }
            }
        }
    }
}
