<?php
/**
 * Copyright © element119. All rights reserved.
 * See LICENCE.txt for licence details.
 */
declare(strict_types=1);

namespace Element119\IndexerDeployConfig\Plugin;

use Element119\IndexerDeployConfig\Model\IndexerConfig;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Indexer\Console\Command\IndexerSetModeCommand;

class SetIndexerModeSchedule extends SetIndexerMode
{
    /**
     * @param IndexerConfig $indexerConfig
     * @param MessageManagerInterface $messageManager
     * @param string $indexerMode
     */
    public function __construct(
        IndexerConfig $indexerConfig,
        MessageManagerInterface $messageManager,
        string $indexerMode = ''
    ) {
        parent::__construct($indexerConfig, $messageManager, IndexerSetModeCommand::INPUT_KEY_SCHEDULE);
    }
}
