<?php
/**
 * Copyright © element119. All rights reserved.
 * See LICENCE.txt for licence details.
 */
declare(strict_types=1);

namespace Element119\IndexerDeployConfig\Service;

use Element119\IndexerDeployConfig\Exception\IndexerConfigurationException;
use Element119\IndexerDeployConfig\Service\IndexerConfigValidator;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;

class IndexerConfigReader
{
    /** @var IndexerConfigValidator */
    private IndexerConfigValidator $indexerConfigValidator;

    /** @var DeploymentConfig */
    private DeploymentConfig $deploymentConfig;

    /**
     * @param IndexerConfigValidator $indexerConfigValidator
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(
        IndexerConfigValidator $indexerConfigValidator,
        DeploymentConfig $deploymentConfig
    ) {
        $this->indexerConfigValidator = $indexerConfigValidator;
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * Read indexer configuration from app/etc/config.php
     *
     * @return array
     * @throws FileSystemException
     * @throws IndexerConfigurationException
     * @throws RuntimeException
     */
    public function getIndexerConfig(): array
    {
        $indexerConfig = $this->deploymentConfig->get('indexers') ?? [];

        if ($indexerConfig) {
            $this->indexerConfigValidator->validateIndexerConfig($indexerConfig);
        }

        return $indexerConfig;
    }

    /**
     * Get the indexers configured to be in the given mode.
     *
     * @param string $mode
     * @param array $indexerConfig
     * @return array
     * @throws FileSystemException
     * @throws IndexerConfigurationException
     * @throws RuntimeException
     */
    public function getIndexersByMode(string $mode, array $indexerConfig = []): array
    {
        $indexerConfig = $indexerConfig ?: $this->getIndexerConfig();

        return array_key_exists($mode, $indexerConfig)
            ? $indexerConfig[$mode]
            : [];
    }
}
