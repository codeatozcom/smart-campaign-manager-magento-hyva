<?php

declare(strict_types=1);

namespace Codeatoz\Campaign\Observer;

use Codeatoz\Campaign\Service\CacheManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ClearCacheObserver implements ObserverInterface
{
    public function __construct(
        private readonly CacheManager $cacheManager
    ) {
    }

    /**
     * Flush all campaign cache entries whenever a campaign is saved or deleted.
     */
    public function execute(Observer $observer): void
    {
        $this->cacheManager->clearAllCampaignCache();
    }
}
