<?php
/**
 * Copyright © element119. All rights reserved.
 * See LICENCE.txt for licence details.
 */
declare(strict_types=1);

namespace Element119\IndexerDeployConfig\Plugin;

use Element119\IndexerDeployConfig\Exception\IndexerConfigurationException;
use Element119\IndexerDeployConfig\Service\IndexerConfigReader;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Indexer\Controller\Adminhtml\Indexer\MassChangelog;
use Magento\Indexer\Controller\Adminhtml\Indexer\MassOnTheFly;
use Magento\Indexer\Model\Indexer;

class SetIndexerMode
{
    /** @var IndexerConfigReader */
    private IndexerConfigReader $indexerConfigReader;

    /** @var MessageManagerInterface */
    private MessageManagerInterface $messageManager;

    /** @var string  */
    private string $indexerMode;

    /**
     * @param IndexerConfigReader $indexerConfigReader
     * @param MessageManagerInterface $messageManager
     * @param string $indexerMode
     */
    public function __construct(
        IndexerConfigReader $indexerConfigReader,
        MessageManagerInterface $messageManager,
        string $indexerMode = ''
    ) {
        $this->indexerConfigReader = $indexerConfigReader;
        $this->messageManager = $messageManager;
        $this->indexerMode = $indexerMode;
    }

    /**
     * Determine whether the indexer should be in scheduled mode or not based on deploy configuration.
     *
     * @param Indexer $subject
     * @param bool $scheduled
     * @return array
     * @throws IndexerConfigurationException
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function beforeSetScheduled(
        Indexer $subject,
        bool $scheduled
    ): array {
        $indexerConfig = $this->indexerConfigReader->getIndexerConfig();
        $indexerId = $subject->getId();

        if ($this->indexerHasMode($indexerConfig, $indexerId, 'schedule')) {
            return [true];
        }

        if ($this->indexerHasMode($indexerConfig, $indexerId, 'save')) {
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
     * @throws FileSystemException
     * @throws IndexerConfigurationException
     * @throws RuntimeException
     */
    public function aroundExecute(
        $subject,
        callable $proceed
    ) {
        if (!($configuredIndexers = $this->indexerConfigReader->getIndexerConfig())) {
            return $proceed();
        }

        $removedIndexers = [];
        $indexerIdsToUpdate = $subject->getRequest()->getParam('indexer_ids');

        foreach ($indexerIdsToUpdate as $key => $indexerToUpdate) {
            if ($this->indexerHasMode($configuredIndexers, $indexerToUpdate, $this->indexerMode)) {
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

    /**
     * Determine if a given indexer is in a given mode, according to a set of given indexer configuration.
     *
     * @param array $indexerConfig
     * @param string $indexerId
     * @param string $mode
     * @return bool
     */
    private function indexerHasMode(array $indexerConfig, string $indexerId, string $mode): bool
    {
        $modeIndexers = array_key_exists($mode, $indexerConfig) ? $indexerConfig[$mode] : [];

        return in_array($indexerId, $modeIndexers);
    }
}
