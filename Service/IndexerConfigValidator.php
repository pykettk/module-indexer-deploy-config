<?php
/**
 * Copyright © element119. All rights reserved.
 * See LICENCE.txt for licence details.
 */
declare(strict_types=1);

namespace Element119\IndexerDeployConfig\Service;

use Element119\IndexerDeployConfig\Exception\IndexerConfigurationException;

class IndexerConfigValidator
{
    /**
     * Validate indexer configuration in app/etc/config.php
     *
     * @param array $indexerConfig
     * @return void
     * @throws IndexerConfigurationException
     */
    public function validateIndexerConfig(array $indexerConfig): void
    {
        $saveIndexers = array_key_exists('save', $indexerConfig)
            ? $indexerConfig['save']
            : [];

        $scheduleIndexers = array_key_exists('schedule', $indexerConfig)
            ? $indexerConfig['schedule']
            : [];

        if ($intersectingIndexers = array_intersect($saveIndexers, $scheduleIndexers)) {
            throw new IndexerConfigurationException(
                __(
                    'Ambiguous indexer configuration found. The following indexer(s) are configured as both save and schedule: %1',
                    implode(', ', $intersectingIndexers)
                )
            );
        }
    }
}
