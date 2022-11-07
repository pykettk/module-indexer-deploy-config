<?php
/**
 * Copyright © element119. All rights reserved.
 * See LICENCE.txt for licence details.
 */
declare(strict_types=1);

namespace Element119\IndexerDeployConfig\Plugin;

use Element119\IndexerDeployConfig\Model\IndexerConfig;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Indexer\Console\Command\IndexerSetModeCommand as IndexerMode;
use Magento\Indexer\Controller\Adminhtml\Indexer\MassChangelog;
use Magento\Indexer\Controller\Adminhtml\Indexer\MassOnTheFly;
use Magento\Indexer\Model\Indexer;

class SetIndexerModeRealtime extends SetIndexerMode
{
    public function __construct(
        IndexerConfig $indexerConfig,
        MessageManagerInterface $messageManager,
        string $indexerMode = ''
    ) {
        parent::__construct('save', $messageManager, $indexerMode);
    }
}
