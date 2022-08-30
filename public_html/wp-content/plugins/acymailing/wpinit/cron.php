<?php

namespace AcyMailing\Init;

class acyCron
{
    public function __construct()
    {
        $ctrl = acym_getVar('string', 'ctrl', '');
        if (acym_level(ACYM_ESSENTIAL) && !acym_isNoTemplate() && $ctrl !== 'cron') {
            $this->callAcyCron();
        }
    }

    public function callAcyCron()
    {
        $config = acym_config();

        $activeCron = $config->get('active_cron', 0);
        if (empty($activeCron)) return;

        $queueType = $config->get('queue_type', 'manual');
        $cronNext = $config->get('cron_next', 0);
        if (empty($cronNext) || $cronNext > time() || $queueType === 'manual') return;

        $cronFrequency = $config->get('cron_frequency', 0);
        $cronBatches = $config->get('queue_batch_auto', 1);

        if (empty($cronFrequency)) return;

        if (intval($cronFrequency) >= 900 && intval($cronBatches) < 2) return;

        acym_asyncCurlCall([acym_frontendLink('cron')]);
    }
}

$acyCron = new acyCron();
