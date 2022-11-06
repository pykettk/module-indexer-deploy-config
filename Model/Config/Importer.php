<?php
/**
 * Copyright © element119. All rights reserved.
 * See LICENCE.txt for licence details.
 */
declare(strict_types=1);

namespace Element119\IndexerDeployConfig\Model\Config;

use InvalidArgumentException;
use Magento\Framework\App\DeploymentConfig\ImporterInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Indexer\Console\Command\IndexerSetModeCommand as IndexerMode;

class Importer implements ImporterInterface
{
    /** @var IndexerRegistry */
    private IndexerRegistry $indexerRegistry;

    /**
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(
        IndexerRegistry $indexerRegistry
    ) {
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * Import indexer config.
     *
     * @param array $indexerConfig
     * @return string[]
     */
    public function import(array $indexerConfig): array
    {
        $messages = [];

        foreach ($indexerConfig as $mode => $indexers) {
            foreach ($indexers as $indexerId) {
                try {
                    $indexer = $this->indexerRegistry->get($indexerId);
                } catch (InvalidArgumentException $e) {
                    $messages[] = "<info>{$indexerId} does not exist, skipping</info>";

                    continue;
                }

                $isRealTimeMode = $mode === IndexerMode::INPUT_KEY_REALTIME;
                $isScheduleMode = $mode === IndexerMode::INPUT_KEY_SCHEDULE;

                // only change index mode when different, slightly more performant
                if ($indexer->isScheduled() && $isRealTimeMode) {
                    $indexer->setScheduled(false);
                    $messages[] = "<info>{$indexerId} updated to \"Update on Save\".</info>";
                } elseif (!$indexer->isScheduled() && $isScheduleMode) {
                    $indexer->setScheduled(true);
                    $messages[] = "<info>{$indexerId} updated to \"Update by Schedule\".</info>";
                }
            }
        }

        return $messages;
    }

    /**
     * Provide user with information regarding what will change after importing indexer config.
     *
     * @param array $indexerConfig
     * @return array|string[]
     */
    public function getWarningMessages(array $indexerConfig): array
    {
        $messages = [];

        $indexersToChangeToRealtime = [];
        $indexersToChangeToSchedule = [];

        $realtimeLockedIndexers = [];
        $scheduleLockedIndexers = [];

        foreach ($indexerConfig as $mode => $indexers) {
            foreach ($indexers as $indexerId) {
                try {
                    $indexer = $this->indexerRegistry->get($indexerId);
                } catch (InvalidArgumentException $e) {
                    continue;
                }

                $isRealTimeMode = $mode === IndexerMode::INPUT_KEY_REALTIME;
                $isScheduleMode = $mode === IndexerMode::INPUT_KEY_SCHEDULE;

                if ($indexer->isScheduled() && $isRealTimeMode) {
                    $indexersToChangeToRealtime[] = $indexerId;
                } elseif (!$indexer->isScheduled() && $isScheduleMode) {
                    $indexersToChangeToSchedule[] = $indexerId;
                }

                if ($isRealTimeMode) {
                    $realtimeLockedIndexers[] = $indexerId;
                } elseif ($isScheduleMode) {
                    $scheduleLockedIndexers[] = $indexerId;
                }
            }
        }

        if ($indexersToChangeToRealtime) {
            $messages[] = "<info>The following indexers will be changed to \"Update on Save\":</info>\n"
                . implode("\n", $indexersToChangeToRealtime);

            // new line separator, "\n" didn't work for some reason ¯\_(ツ)_/¯
            $messages[] = '';
        }

        if ($indexersToChangeToSchedule) {
            $messages[] = "<info>The following indexers will be changed to \"Update by Schedule\":</info>\n"
                . implode("\n", $indexersToChangeToSchedule);

            // new line separator, "\n" didn't work for some reason ¯\_(ツ)_/¯
            $messages[] = '';
        }

        if ($realtimeLockedIndexers) {
            $messages[] = "<info>The following indexers will be locked to \"Update on Save\":</info>\n"
                . implode("\n", $realtimeLockedIndexers);

            // new line separator, "\n" didn't work for some reason ¯\_(ツ)_/¯
            $messages[] = '';
        }

        if ($scheduleLockedIndexers) {
            $messages[] = "<info>The following indexers will be locked to \"Update by Schedule\":</info>\n"
                . implode("\n", $scheduleLockedIndexers);

            // new line separator, "\n" didn't work for some reason ¯\_(ツ)_/¯
            $messages[] = '';
        }

        if ($messages) {
            $messages[] = '<info>All other indexers will be unlocked.</info>';
        }

        return $messages;
    }
}
