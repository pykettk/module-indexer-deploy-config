<?php
/**
 * Copyright © element119. All rights reserved.
 * See LICENCE.txt for licence details.
 */
declare(strict_types=1);

namespace Element119\IndexerDeployConfig\Model\Config;

use Element119\IndexerDeployConfig\Model\IndexerConfig;
use Magento\Framework\App\DeploymentConfig\ValidatorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Indexer\Console\Command\IndexerSetModeCommand as IndexerMode;

class Validator implements ValidatorInterface
{
    private IndexerConfig $indexerConfig;

    public function __construct(
        ?IndexerConfig $indexerConfig = null
    ) {
        $this->indexerConfig = $indexerConfig ?: ObjectManager::getInstance()->get(IndexerConfig::class);
    }

    /**
     * Validate indexer configuration.
     *
     * @param array $indexerConfig
     * @return array|string[]
     */
    public function validate(array $indexerConfig): array
    {
        $messages = [];

        if (!$indexerConfig) {
            return $messages;
        }

        $realtimeIndexers = array_key_exists(IndexerMode::INPUT_KEY_REALTIME, $indexerConfig)
            ? $indexerConfig[IndexerMode::INPUT_KEY_REALTIME]
            : [];

        $scheduleIndexers = array_key_exists(IndexerMode::INPUT_KEY_SCHEDULE, $indexerConfig)
            ? $indexerConfig[IndexerMode::INPUT_KEY_SCHEDULE]
            : [];

        if ($intersectingIndexers = array_intersect($realtimeIndexers, $scheduleIndexers)) {
            $messages[] = __(
                'Ambiguous indexer configuration found. The following indexer(s) are configured as both realtime and schedule: %1',
                implode(', ', $intersectingIndexers)
            );
        }

        if ($disallowedOnSaveIndexers = array_intersect($realtimeIndexers, $this->indexerConfig->getForbiddenOnSaveIndexers())) {
            $messages[] = __(
                'Discouraged indexer configuration found. The following indexer(s) are configured to be realtime but should be schedule: %1',
                implode(', ', $disallowedOnSaveIndexers)
            );
        }

        if ($disallowedByScheduleIndexers = array_intersect($scheduleIndexers, $this->indexerConfig->getForbiddenByScheduleIndexers())) {
            $messages[] = __(
                'Discouraged indexer configuration found. The following indexer(s) are configured to be schedule but should be realtime: %1',
                implode(', ', $disallowedByScheduleIndexers)
            );
        }

        return $messages;
    }
}
