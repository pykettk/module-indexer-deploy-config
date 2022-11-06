<?php
/**
 * Copyright © element119. All rights reserved.
 * See LICENCE.txt for licence details.
 */
declare(strict_types=1);

namespace Element119\IndexerDeployConfig\Block\Backend\Grid\Column\Renderer;

use Element119\IndexerDeployConfig\Model\IndexerConfig;
use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Magento\Framework\Phrase;

class ModeLocked extends AbstractRenderer
{
    /** @var IndexerConfig */
    private IndexerConfig $indexerConfig;

    /**
     * @param Context $context
     * @param IndexerConfig $indexerConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        IndexerConfig $indexerConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->indexerConfig = $indexerConfig;
    }

    /**
     * @param DataObject $row
     * @return Phrase
     */
    public function render(DataObject $row): Phrase
    {
        $indexerConfig = $this->indexerConfig->getIndexerConfig();
        $saveIndexers = $this->indexerConfig->getIndexersByMode('save', $indexerConfig);
        $scheduleIndexers = $this->indexerConfig->getIndexersByMode('schedule', $indexerConfig);

        $indexerId = $row->getData('indexer_id');

        return in_array($indexerId, $scheduleIndexers) || in_array($indexerId, $saveIndexers)
            ? __('Yes')
            : __('No');
    }
}
