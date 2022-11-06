<?php
/**
 * Copyright © element119. All rights reserved.
 * See LICENCE.txt for licence details.
 */
declare(strict_types=1);

namespace Element119\IndexerDeployConfig\Block\Backend\Grid\Column\Renderer;

use Element119\IndexerDeployConfig\Exception\IndexerConfigurationException;
use Element119\IndexerDeployConfig\Service\IndexerConfigReader;
use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Phrase;

class ModeLocked extends AbstractRenderer
{
    /** @var IndexerConfigReader */
    private IndexerConfigReader $indexerConfigReader;

    /**
     * @param Context $context
     * @param IndexerConfigReader $indexerConfigReader
     * @param array $data
     */
    public function __construct(
        Context $context,
        IndexerConfigReader $indexerConfigReader,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->indexerConfigReader = $indexerConfigReader;
    }

    /**
     * @param DataObject $row
     * @return Phrase
     */
    public function render(DataObject $row): Phrase
    {
        try {
            $indexerConfig = $this->indexerConfigReader->getIndexerConfig();
            $saveIndexers = $this->indexerConfigReader->getIndexersByMode('save', $indexerConfig);
            $scheduleIndexers = $this->indexerConfigReader->getIndexersByMode('schedule', $indexerConfig);
        } catch (IndexerConfigurationException | FileSystemException | RuntimeException $e) {
            return __('Could not retrieve indexer deploy config.');
        }

        $indexerId = $row->getData('indexer_id');

        return in_array($indexerId, $scheduleIndexers) || in_array($indexerId, $saveIndexers)
            ? __('Yes')
            : __('No');
    }
}
