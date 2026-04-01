<?php

declare(strict_types=1);

namespace Codeatoz\Campaign\Cron;

use Codeatoz\Campaign\Service\ActivationManager;

class CampaignActivationCron
{
    public function __construct(
        private readonly ActivationManager $activationManager
    ) {
    }

    /**
     * Entry point called by Magento cron scheduler every 5 minutes.
     */
    public function execute(): void
    {
        $this->activationManager->execute();
    }
}
