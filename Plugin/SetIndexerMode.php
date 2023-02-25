<?php
/**
 * Copyright © element119. All rights reserved.
 * See LICENCE.txt for licence details.
 */
declare(strict_types=1);

namespace Element119\IndexerDeployConfig\Plugin;

use Element119\IndexerDeployConfig\Model\IndexerConfig;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Mview\View\StateInterface;
use Magento\Indexer\Console\Command\IndexerSetModeCommand as IndexerMode;
use Magento\Indexer\Controller\Adminhtml\Indexer\MassChangelog;
use Magento\Indexer\Controller\Adminhtml\Indexer\MassOnTheFly;

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
     * Ensure indexer mode remains in the mode defined by deploy config.
     *
     * @param StateInterface $subject
     * @param string $mode
     * @return string[]
     */
    public function beforeSetMode(
        StateInterface $subject,
        string $mode
    ): array {
        if (!$this->indexerConfig->getIndexerConfig()
            || !($indexerId = $this->indexerConfig->getIndexerIdForViewId($subject->getViewId()))
            || !$this->indexerConfig->isIndexerLocked($indexerId)
        ) {
            return [$mode]; // no indexers should be locked, could not find indexer ID, or indexer is not mode-locked
        }

        $isBeingScheduled = $mode === 'enabled';
        $shouldBeScheduled = $this->indexerConfig->indexerHasMode($indexerId, IndexerMode::INPUT_KEY_SCHEDULE);
        $shouldBeRealTime = $this->indexerConfig->indexerHasMode($indexerId, IndexerMode::INPUT_KEY_REALTIME);

        if ($shouldBeScheduled && !$isBeingScheduled) {
            return ['enabled']; // maintain on schedule status
        } else if ($shouldBeRealTime && $isBeingScheduled) {
            return ['disabled']; // maintain on save status
        }

        return [$mode];
    }

    /**
     * Ensure indexer mode remains in the mode defined by deploy config.
     *
     * @param $subject
     * @param $key
     * @param $value
     * @return array
     */
    public function beforeSetData(
        $subject,
        $key,
        $value = null
    ) {
        if (!($subject instanceof StateInterface)
            || $key !== 'mode'
            || !$this->indexerConfig->getIndexerConfig()
            || !($indexerId = $this->indexerConfig->getIndexerIdForViewId($subject->getViewId()))
            || !$this->indexerConfig->isIndexerLocked($indexerId)
        ) {
            // not an indexer state, not setting mode data, no indexers should be locked, cannot find indexer ID, or indexer is not mode-locked
            return [$key, $value];
        }

        $isBeingScheduled = null;
        $shouldBeScheduled = $this->indexerConfig->indexerHasMode($indexerId, IndexerMode::INPUT_KEY_SCHEDULE);
        $shouldBeRealTime = $this->indexerConfig->indexerHasMode($indexerId, IndexerMode::INPUT_KEY_REALTIME);

        if ($key === (array)$key) {
            $mode = array_key_exists('mode', $key) ? $key['mode'] : null;

            if ($mode !== null) {
                $isBeingScheduled = $mode === 'enabled';
            }
        } elseif ($value !== null) {
            $isBeingScheduled = $value === 'enabled';
        }

        if ($isBeingScheduled !== null) {
            if ($shouldBeScheduled && !$isBeingScheduled) {
                $value = 'enabled'; // maintain on schedule status
            } else if ($shouldBeRealTime && $isBeingScheduled) {
                $value = 'disabled'; // maintain on save status
            }
        }

        return [$key, $value];
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
