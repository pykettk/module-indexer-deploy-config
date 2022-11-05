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
use Magento\Indexer\Model\Indexer;

class SetIndexerMode
{
    /** @var IndexerConfigReader */
    private IndexerConfigReader $indexerConfigReader;

    /**
     * @param IndexerConfigReader $indexerConfigReader
     */
    public function __construct(
        IndexerConfigReader $indexerConfigReader
    ) {
        $this->indexerConfigReader = $indexerConfigReader;
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
