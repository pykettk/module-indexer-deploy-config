<?php
/**
 * Copyright © element119. All rights reserved.
 * See LICENCE.txt for licence details.
 */
declare(strict_types=1);

namespace Element119\IndexerDeployConfig\Exception;

use Exception;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\Phrase;

class IndexerConfigurationException extends ConfigurationMismatchException
{
    /**
     * @param Phrase|null $phrase
     * @param Exception|null $cause
     * @param $code
     */
    public function __construct(
        Phrase $phrase = null,
        Exception $cause = null,
        $code = 0
    ) {
        if ($phrase === null) {
            $phrase = new Phrase('There is a problem with the indexer configuration in app/etc/config.php');
        }

        parent::__construct($phrase, $cause, $code);
    }
}
