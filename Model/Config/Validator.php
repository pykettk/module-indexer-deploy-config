<?php
/**
 * Copyright © element119. All rights reserved.
 * See LICENCE.txt for licence details.
 */
declare(strict_types=1);

namespace Element119\IndexerDeployConfig\Model\Config;

use Magento\Framework\App\DeploymentConfig\ValidatorInterface;
use Magento\Indexer\Console\Command\IndexerSetModeCommand as IndexerMode;

class Validator implements ValidatorInterface
{
    /**
     * Validate indexer configuration.
     *
     * @param array $indexerConfig
     * @return array|string[]
     */
    public function validate(array $indexerConfig): array
    {
        if (!$indexerConfig) {
            return [];
        }

        $realtimeIndexers = array_key_exists(IndexerMode::INPUT_KEY_REALTIME, $indexerConfig)
            ? $indexerConfig[IndexerMode::INPUT_KEY_REALTIME]
            : [];

        $scheduleIndexers = array_key_exists(IndexerMode::INPUT_KEY_SCHEDULE, $indexerConfig)
            ? $indexerConfig[IndexerMode::INPUT_KEY_SCHEDULE]
            : [];

        if ($intersectingIndexers = array_intersect($realtimeIndexers, $scheduleIndexers)) {
            return [__(
                'Ambiguous indexer configuration found. The following indexer(s) are configured as both realtime and schedule: %1',
                implode(', ', $intersectingIndexers)
            )];
        }

        return [];
    }
}
