<?php

declare(strict_types=1);

namespace Codeatoz\Campaign\Model;

use Codeatoz\Campaign\Api\Data\CampaignInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;

class Campaign extends AbstractModel implements CampaignInterface, IdentityInterface
{
    public const CACHE_TAG = 'codeatoz_campaign';

    protected $_cacheTag    = self::CACHE_TAG;
    protected $_eventPrefix = 'codeatoz_campaign_model';
    protected $_eventObject = 'campaign';

    protected function _construct(): void
    {
        $this->_init(\Codeatoz\Campaign\Model\ResourceModel\Campaign::class);
    }

    public function getIdentities(): array
    {
        return [self::CACHE_TAG, self::CACHE_TAG . '_' . $this->getId()];
    }

    // ----------------------------------------------------------------
    // Interface getters / setters
    // ----------------------------------------------------------------

    public function getCampaignId(): ?int
    {
        $id = $this->getData(self::CAMPAIGN_ID);
        return $id !== null ? (int) $id : null;
    }

    public function setCampaignId(int $campaignId): self
    {
        return $this->setData(self::CAMPAIGN_ID, $campaignId);
    }

    public function getName(): string
    {
        return (string) $this->getData(self::NAME);
    }

    public function setName(string $name): self
    {
        return $this->setData(self::NAME, $name);
    }

    public function getCampaignType(): string
    {
        return (string) ($this->getData(self::CAMPAIGN_TYPE) ?: 'general');
    }

    public function setCampaignType(string $type): self
    {
        return $this->setData(self::CAMPAIGN_TYPE, $type);
    }

    public function getStatus(): int
    {
        return (int) $this->getData(self::STATUS);
    }

    public function setStatus(int $status): self
    {
        return $this->setData(self::STATUS, $status);
    }

    public function getStartDate(): string
    {
        return (string) $this->getData(self::START_DATE);
    }

    public function setStartDate(string $startDate): self
    {
        return $this->setData(self::START_DATE, $startDate);
    }

    public function getEndDate(): string
    {
        return (string) $this->getData(self::END_DATE);
    }

    public function setEndDate(string $endDate): self
    {
        return $this->setData(self::END_DATE, $endDate);
    }

    public function getPriority(): int
    {
        return (int) $this->getData(self::PRIORITY);
    }

    public function setPriority(int $priority): self
    {
        return $this->setData(self::PRIORITY, $priority);
    }

    public function getConfigJson(): ?string
    {
        return $this->getData(self::CONFIG_JSON);
    }

    public function setConfigJson(?string $configJson): self
    {
        return $this->setData(self::CONFIG_JSON, $configJson);
    }

    public function getStoreIds(): ?string
    {
        return $this->getData(self::STORE_IDS);
    }

    public function setStoreIds(?string $storeIds): self
    {
        return $this->setData(self::STORE_IDS, $storeIds);
    }

    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    public function getUpdatedAt(): ?string
    {
        return $this->getData(self::UPDATED_AT);
    }

    // ----------------------------------------------------------------
    // Helper methods
    // ----------------------------------------------------------------

    /**
     * Check if the campaign is currently active based on datetime boundaries.
     */
    public function isCurrentlyActive(): bool
    {
        $now   = (new \DateTime())->getTimestamp();
        $start = strtotime($this->getStartDate());
        $end   = strtotime($this->getEndDate());

        if ($start === false || $end === false) {
            return false;
        }

        return $now >= $start && $now <= $end;
    }

    /**
     * Decode and return config_json as an array.
     */
    public function getConfigData(): array
    {
        $json = $this->getConfigJson();
        if (empty($json)) {
            return [];
        }

        try {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            return is_array($decoded) ? $decoded : [];
        } catch (\JsonException $e) {
            return [];
        }
    }

    /**
     * Return promo bar configuration array.
     */
    public function getPromoBarConfig(): array
    {
        return $this->getConfigData()['promo_bar'] ?? [];
    }

    /**
     * Return product badge configuration array.
     */
    public function getProductBadgeConfig(): array
    {
        return $this->getConfigData()['product_badge'] ?? [];
    }
}
