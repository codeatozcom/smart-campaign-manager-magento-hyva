<?php

declare(strict_types=1);

namespace Codeatoz\Campaign\Service;

use Magento\Framework\App\CacheInterface;

class CacheManager
{
    private const CACHE_KEY_PREFIX = 'codeatoz_active_campaign_';
    private const CACHE_TAG        = 'codeatoz_campaign';
    private const CACHE_LIFETIME   = 3600;

    public function __construct(
        private readonly CacheInterface $cache
    ) {
    }

    /**
     * Retrieve the active campaign ID for a given store from cache.
     * Returns null if no cached value exists.
     */
    public function getActiveCampaignId(int $storeId): ?int
    {
        $value = $this->cache->load($this->buildKey($storeId));

        if ($value === false || $value === null || $value === '') {
            return null;
        }

        $id = (int) $value;
        return $id > 0 ? $id : null;
    }

    /**
     * Persist the active campaign ID for a given store into cache.
     */
    public function setActiveCampaignId(int $storeId, int $campaignId): void
    {
        $this->cache->save(
            (string) $campaignId,
            $this->buildKey($storeId),
            [self::CACHE_TAG],
            self::CACHE_LIFETIME
        );
    }

    /**
     * Remove the cached active campaign entry for a specific store.
     */
    public function clearActiveCampaign(int $storeId): void
    {
        $this->cache->remove($this->buildKey($storeId));
    }

    /**
     * Invalidate all campaign-tagged cache entries across all stores.
     */
    public function clearAllCampaignCache(): void
    {
        $this->cache->clean(self::CACHE_TAG);
    }

    /**
     * Build the cache key string for a given store ID.
     */
    private function buildKey(int $storeId): string
    {
        return self::CACHE_KEY_PREFIX . $storeId;
    }
}
