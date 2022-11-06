<?php
/**
 * Copyright © element119. All rights reserved.
 * See LICENCE.txt for licence details.
 */
declare(strict_types=1);

namespace Element119\IndexerDeployConfig\Model;

use Element119\IndexerDeployConfig\Exception\IndexerConfigurationException;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;
use Psr\Log\LoggerInterface;

class IndexerConfig
{
    /** @var DeploymentConfig */
    private DeploymentConfig $deploymentConfig;

    /** @var LoggerInterface */
    private LoggerInterface $logger;

    /**
     * @param DeploymentConfig $deploymentConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        DeploymentConfig $deploymentConfig,
        LoggerInterface $logger
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->logger = $logger;
    }

    /**
     * Read indexer configuration from app/etc/config.php
     *
     * @return array
     */
    public function getIndexerConfig(): array
    {
        try {
            return $this->deploymentConfig->get('indexers') ?? [];
        } catch (FileSystemException | RunTimeException $e) {
            $this->logger->error(__('Could not load indexer configuration from app/etc/config.php'));

            return [];
        }
    }

    /**
     * Get the indexers configured to be in the given mode.
     *
     * @param string $mode
     * @param array $indexerConfig
     * @return array
     */
    public function getIndexersByMode(string $mode, array $indexerConfig = []): array
    {
        $indexerConfig = $indexerConfig ?: $this->getIndexerConfig();

        return array_key_exists($mode, $indexerConfig)
            ? $indexerConfig[$mode]
            : [];
    }

    /**
     * Validate indexer configuration in app/etc/config.php
     *
     * @return void
     * @throws IndexerConfigurationException
     */
    public function validateIndexerConfig(): void
    {
        $saveIndexers = $this->getIndexersByMode('save');
        $scheduleIndexers = $this->getIndexersByMode('schedule');

        if ($intersectingIndexers = array_intersect($saveIndexers, $scheduleIndexers)) {
            throw new IndexerConfigurationException(
                __(
                    'Ambiguous indexer configuration found. The following indexer(s) are configured as both save and schedule: %1',
                    implode(', ', $intersectingIndexers)
                )
            );
        }
    }

    /**
     * Determine if a given indexer is in a given mode, according to a set of given indexer configuration.
     *
     * @param string $indexerId
     * @param string $mode
     * @param array $indexerConfig
     * @return bool
     */
    public function indexerHasMode(string $indexerId, string $mode, array $indexerConfig = []): bool
    {
        $indexerConfig = $indexerConfig ?: $this->getIndexerConfig();
        $modeIndexers = array_key_exists($mode, $indexerConfig) ? $indexerConfig[$mode] : [];

        return in_array($indexerId, $modeIndexers);
    }
}
