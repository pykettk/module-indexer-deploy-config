<?php
/**
 * Copyright © element119. All rights reserved.
 * See LICENCE.txt for licence details.
 */
declare(strict_types=1);

namespace Element119\IndexerDeployConfig\Scope;

use Magento\Framework\App\Config\ScopeConfigInterface;

class ModuleConfig
{
    private const XML_PATH_CRON_FALLBACK_ENABLED = 'system/e119_indexer_deploy_config/cron_enable';

    /** @var ScopeConfigInterface */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return bool
     */
    public function isCronFallbackEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_CRON_FALLBACK_ENABLED);
    }
}
