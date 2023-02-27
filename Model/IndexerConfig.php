<?php
/**
 * Copyright © element119. All rights reserved.
 * See LICENCE.txt for licence details.
 */
declare(strict_types=1);

namespace Element119\IndexerDeployConfig\Model;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Indexer\Console\Command\IndexerSetModeCommand as IndexerMode;
use Magento\Indexer\Model\Indexer\CollectionFactory as IndexerCollectionFactory;
use Magento\Indexer\Model\Indexer;
use Psr\Log\LoggerInterface;

class IndexerConfig
{
    /** @var DeploymentConfig */
    private DeploymentConfig $deploymentConfig;

    /** @var IndexerCollectionFactory */
    private IndexerCollectionFactory $indexerCollectionFactory;

    /** @var LoggerInterface */
    private LoggerInterface $logger;

    /** @var array */
    private array $indexerConfig = [];

    /** @var array */
    private array $flatIndexerConfig = [];

    /** @var array */
    private array $viewIdToIndexerIdMap = [];

    /**
     * @param DeploymentConfig $deploymentConfig
     * @param IndexerCollectionFactory $indexerCollectionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        DeploymentConfig $deploymentConfig,
        IndexerCollectionFactory $indexerCollectionFactory,
        LoggerInterface $logger
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->indexerCollectionFactory = $indexerCollectionFactory;
        $this->logger = $logger;
    }

    /**
     * Read indexer configuration from app/etc/config.php
     *
     * @return array
     */
    public function getIndexerConfig(): array
    {
        if (!$this->indexerConfig) {
            try {
                $this->indexerConfig = $this->deploymentConfig->get('indexers');
            } catch (FileSystemException|RunTimeException $e) {
                $this->logger->error(__('Could not load indexer configuration from app/etc/config.php'));
                $this->indexerConfig = [];
            }
        }

        return $this->indexerConfig;
    }

    /**
     * Get indexer config as indexerId => lockedMode pairs.
     *
     * @return array
     */
    public function getFlatIndexerConfig(): array
    {
        if (!$this->flatIndexerConfig) {
            $flatIndexerConfig = [];
            $indexerConfig = $this->getIndexerConfig();

            foreach ($indexerConfig as $mode => $indexers) {
                foreach ($indexers as $indexer) {
                    $flatIndexerConfig[$indexer] = $mode;
                }
            }

            $this->flatIndexerConfig = $flatIndexerConfig;
        }

        return $this->flatIndexerConfig;
    }

    /**
     * Determine whether a given indexer ID should be locked to a particular mode.
     *
     * @param string $indexerId
     * @return bool
     */
    public function isIndexerLocked(string $indexerId): bool
    {
        return array_key_exists($indexerId, $this->getFlatIndexerConfig());
    }

    /**
     * Map view ID to indexer ID.
     *
     * @return array
     */
    public function getViewIdToIndexerIdMap(): array
    {
        if (!$this->viewIdToIndexerIdMap) {
            /** @var Indexer $indexer */
            foreach ($this->indexerCollectionFactory->create()->getItems() as $indexer) {
                $this->viewIdToIndexerIdMap[$indexer->getViewId()] = $indexer->getId();
            }
        }

        return $this->viewIdToIndexerIdMap;
    }

    /**
     * Get the indexer ID for the given view ID.
     *
     * @param string|null $viewId
     * @return string
     */
    public function getIndexerIdForViewId(?string $viewId): string
    {
        $map = $this->getViewIdToIndexerIdMap();

        return array_key_exists($viewId, $map) ? $map[$viewId] : '';
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
