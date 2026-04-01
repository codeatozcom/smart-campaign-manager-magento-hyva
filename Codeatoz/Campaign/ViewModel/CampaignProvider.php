<?php

declare(strict_types=1);

namespace Codeatoz\Campaign\ViewModel;

use Codeatoz\Campaign\Api\CampaignRepositoryInterface;
use Codeatoz\Campaign\Api\Data\CampaignInterface;
use Codeatoz\Campaign\Service\CacheManager;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class CampaignProvider implements ArgumentInterface
{
    private ?CampaignInterface $activeCampaign = null;
    private bool $loaded = false;

    public function __construct(
        private readonly CampaignRepositoryInterface $campaignRepository,
        private readonly CacheManager $cacheManager,
        private readonly StoreManagerInterface $storeManager,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Load and return the active campaign for the current store.
     * Result is cached in-memory for the lifetime of the request.
     */
    public function getActiveCampaign(): ?CampaignInterface
    {
        if ($this->loaded) {
            return $this->activeCampaign;
        }

        $this->loaded = true;

        try {
            $storeId    = (int) $this->storeManager->getStore()->getId();
            $campaignId = $this->cacheManager->getActiveCampaignId($storeId);

            if ($campaignId !== null) {
                try {
                    $this->activeCampaign = $this->campaignRepository->getById($campaignId);
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    // Cached ID points to a deleted campaign — fall through to direct query
                    $this->activeCampaign = null;
                }
            }

            // Fallback: cron has not run yet or cache was invalidated
            if ($this->activeCampaign === null) {
                $this->activeCampaign = $this->campaignRepository->getActiveCampaignForStore($storeId);
            }
        } catch (\Exception $e) {
            $this->logger->error(
                'Codeatoz_Campaign: CampaignProvider could not load active campaign.',
                ['exception' => $e->getMessage()]
            );
            $this->activeCampaign = null;
        }

        return $this->activeCampaign;
    }

    /**
     * Indicate whether the promo bar feature is enabled in the active campaign.
     */
    public function isPromoBarEnabled(): bool
    {
        $campaign = $this->getActiveCampaign();
        if ($campaign === null) {
            return false;
        }

        return (bool) ($campaign->getPromoBarConfig()['enabled'] ?? false);
    }

    /**
     * Return the full promo bar configuration array.
     */
    public function getPromoBarConfig(): array
    {
        $campaign = $this->getActiveCampaign();
        if ($campaign === null) {
            return [];
        }

        return $campaign->getPromoBarConfig();
    }

    /**
     * Indicate whether the product badge feature is enabled in the active campaign.
     */
    public function isProductBadgeEnabled(): bool
    {
        $campaign = $this->getActiveCampaign();
        if ($campaign === null) {
            return false;
        }

        return (bool) ($campaign->getProductBadgeConfig()['enabled'] ?? false);
    }

    /**
     * Return the full product badge configuration array.
     */
    public function getProductBadgeConfig(): array
    {
        $campaign = $this->getActiveCampaign();
        if ($campaign === null) {
            return [];
        }

        return $campaign->getProductBadgeConfig();
    }

    /**
     * Return the campaign end date as an ISO 8601 UTC string suitable for Alpine.js countdown.
     * Returns null if no active campaign is loaded.
     */
    public function getCampaignEndDate(): ?string
    {
        $campaign = $this->getActiveCampaign();
        if ($campaign === null) {
            return null;
        }

        $endDate = $campaign->getEndDate();
        if (empty($endDate)) {
            return null;
        }

        try {
            $dt = new \DateTime($endDate, new \DateTimeZone('UTC'));
            return $dt->format(\DateTime::ATOM);
        } catch (\Exception $e) {
            return null;
        }
    }
}
