<?php

declare(strict_types=1);

namespace Codeatoz\Campaign\Service;

use Codeatoz\Campaign\Api\CampaignRepositoryInterface;
use Codeatoz\Campaign\Model\Source\Status;
use Codeatoz\Campaign\Model\ResourceModel\Campaign\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class ActivationManager
{
    public function __construct(
        private readonly CampaignRepositoryInterface $campaignRepository,
        private readonly CacheManager $cacheManager,
        private readonly StoreManagerInterface $storeManager,
        private readonly CollectionFactory $collectionFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Run every 5 minutes via cron.
     * 1. Auto-transition campaign statuses based on current datetime.
     * 2. Cache the winning Active campaign per store.
     */
    public function execute(): void
    {
        try {
            $this->updateCampaignStatuses();
        } catch (\Exception $e) {
            $this->logger->error('Codeatoz_Campaign: Status update failed.', ['exception' => $e->getMessage()]);
        }

        try {
            $stores = $this->storeManager->getStores(true);
        } catch (\Exception $e) {
            $this->logger->error('Codeatoz_Campaign: Could not load stores.', ['exception' => $e->getMessage()]);
            return;
        }

        foreach ($stores as $store) {
            $storeId = (int) $store->getId();
            try {
                $this->processStore($storeId);
            } catch (\Exception $e) {
                $this->logger->error(
                    'Codeatoz_Campaign: Store processing failed for store ' . $storeId,
                    ['exception' => $e->getMessage()]
                );
            }
        }
    }

    /**
     * Auto-transition campaign statuses:
     *
     * Scheduled(1) where now is within [start_date, end_date]  → Active(2)
     * Active(2) or Scheduled(1) where end_date < now           → Expired(3)
     * Active(2) where start_date > now (set active too early)  → Scheduled(1)
     */
    private function updateCampaignStatuses(): void
    {
        $now = (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');

        // Scheduled → Active (window has opened)
        $toActivate = $this->collectionFactory->create();
        $toActivate->addFieldToFilter('status', ['eq' => Status::SCHEDULED]);
        $toActivate->addFieldToFilter('start_date', ['lteq' => $now]);
        $toActivate->addFieldToFilter('end_date',   ['gteq' => $now]);
        foreach ($toActivate->getItems() as $campaign) {
            try {
                $campaign->setStatus(Status::ACTIVE);
                $this->campaignRepository->save($campaign);
                $this->logger->debug('Codeatoz_Campaign: Campaign ' . $campaign->getCampaignId() . ' → Active');
            } catch (\Exception $e) {
                $this->logger->error('Codeatoz_Campaign: Activate failed #' . $campaign->getCampaignId(), ['e' => $e->getMessage()]);
            }
        }

        // Active/Scheduled → Expired (end_date passed)
        $toExpire = $this->collectionFactory->create();
        $toExpire->addFieldToFilter('status', ['in' => [Status::SCHEDULED, Status::ACTIVE]]);
        $toExpire->addFieldToFilter('end_date', ['lt' => $now]);
        foreach ($toExpire->getItems() as $campaign) {
            try {
                $campaign->setStatus(Status::EXPIRED);
                $this->campaignRepository->save($campaign);
                $this->logger->debug('Codeatoz_Campaign: Campaign ' . $campaign->getCampaignId() . ' → Expired');
            } catch (\Exception $e) {
                $this->logger->error('Codeatoz_Campaign: Expire failed #' . $campaign->getCampaignId(), ['e' => $e->getMessage()]);
            }
        }

        // Active but start_date in the future → revert to Scheduled
        $premature = $this->collectionFactory->create();
        $premature->addFieldToFilter('status', ['eq' => Status::ACTIVE]);
        $premature->addFieldToFilter('start_date', ['gt' => $now]);
        foreach ($premature->getItems() as $campaign) {
            try {
                $campaign->setStatus(Status::SCHEDULED);
                $this->campaignRepository->save($campaign);
                $this->logger->debug('Codeatoz_Campaign: Campaign ' . $campaign->getCampaignId() . ' reverted → Scheduled');
            } catch (\Exception $e) {
                $this->logger->error('Codeatoz_Campaign: Revert failed #' . $campaign->getCampaignId(), ['e' => $e->getMessage()]);
            }
        }
    }

    /**
     * Cache the highest-priority Active campaign for a store.
     */
    private function processStore(int $storeId): void
    {
        $campaign = $this->campaignRepository->getActiveCampaignForStore($storeId);

        if ($campaign !== null) {
            $this->cacheManager->setActiveCampaignId($storeId, (int) $campaign->getCampaignId());
        } else {
            $this->cacheManager->clearActiveCampaign($storeId);
        }
    }
}
