<?php
/**
 * Copyright © element119. All rights reserved.
 * See LICENCE.txt for licence details.
 */
declare(strict_types=1);

namespace Element119\IndexerDeployConfig\Plugin;

use Element119\IndexerDeployConfig\Model\IndexerConfig;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Indexer\Controller\Adminhtml\Indexer\MassChangelog;
use Magento\Indexer\Controller\Adminhtml\Indexer\MassOnTheFly;
use Magento\Indexer\Model\Indexer;

class SetIndexerMode
{
    /** @var IndexerConfig */
    private IndexerConfig $indexerConfig;

    /** @var MessageManagerInterface */
    private MessageManagerInterface $messageManager;

    /** @var string  */
    private string $indexerMode;

    /**
     * @param IndexerConfig $indexerConfig
     * @param MessageManagerInterface $messageManager
     * @param string $indexerMode
     */
    public function __construct(
        IndexerConfig $indexerConfig,
        MessageManagerInterface $messageManager,
        string $indexerMode = ''
    ) {
        $this->indexerConfig = $indexerConfig;
        $this->messageManager = $messageManager;
        $this->indexerMode = $indexerMode;
    }

    /**
     * Determine whether the indexer should be in scheduled mode or not based on deploy configuration.
     *
     * @param Indexer $subject
     * @param bool $scheduled
     * @return array
     */
    public function beforeSetScheduled(
        Indexer $subject,
        bool $scheduled
    ): array {
        $indexerConfig = $this->indexerConfig->getIndexerConfig();
        $indexerId = $subject->getId();

        if ($this->indexerConfig->indexerHasMode($indexerId, 'schedule', $indexerConfig)) {
            return [true];
        }

        if ($this->indexerConfig->indexerHasMode($indexerId, 'save', $indexerConfig)) {
            return [false];
        }

        return [$scheduled];
    }

    /**
     * Prevent indexers from changing modes via the admin based on deploy configuration.
     *
     * @param MassChangelog|MassOnTheFly $subject
     * @param callable $proceed
     * @return void
     */
    public function aroundExecute(
        $subject,
        callable $proceed
    ) {
        if (!($configuredIndexers = $this->indexerConfig->getIndexerConfig())) {
            return $proceed();
        }

        $removedIndexers = [];
        $indexerIdsToUpdate = $subject->getRequest()->getParam('indexer_ids');

        foreach ($indexerIdsToUpdate as $key => $indexerToUpdate) {
            if ($this->indexerConfig->indexerHasMode($indexerToUpdate, $this->indexerMode, $configuredIndexers)) {
                unset($indexerIdsToUpdate[$key]);
                $removedIndexers[] = $indexerToUpdate;
            }
        }

        if ($removedIndexers) {
            $subject->getRequest()->setParam('indexer_ids', $indexerIdsToUpdate);
            $this->messageManager->addErrorMessage(
                __(
                    'Cannot update the following indexer(s) because they are locked by configuration: %1',
                    implode(', ', $removedIndexers)
                )
            );
        }

        $proceed();
    }
}
