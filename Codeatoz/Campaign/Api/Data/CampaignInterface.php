<?php

declare(strict_types=1);

namespace Codeatoz\Campaign\Api\Data;

interface CampaignInterface
{
    public const CAMPAIGN_ID   = 'campaign_id';
    public const NAME          = 'name';
    public const CAMPAIGN_TYPE = 'campaign_type';
    public const STATUS        = 'status';
    public const START_DATE    = 'start_date';
    public const END_DATE      = 'end_date';
    public const PRIORITY      = 'priority';
    public const CONFIG_JSON   = 'config_json';
    public const STORE_IDS     = 'store_ids';
    public const CREATED_AT    = 'created_at';
    public const UPDATED_AT    = 'updated_at';

    public function getCampaignId(): ?int;
    public function setCampaignId(int $campaignId): self;
    public function getName(): string;
    public function setName(string $name): self;
    public function getCampaignType(): string;
    public function setCampaignType(string $type): self;
    public function getStatus(): int;
    public function setStatus(int $status): self;
    public function getStartDate(): string;
    public function setStartDate(string $startDate): self;
    public function getEndDate(): string;
    public function setEndDate(string $endDate): self;
    public function getPriority(): int;
    public function setPriority(int $priority): self;
    public function getConfigJson(): ?string;
    public function setConfigJson(?string $configJson): self;
    public function getStoreIds(): ?string;
    public function setStoreIds(?string $storeIds): self;
    public function getCreatedAt(): ?string;
    public function getUpdatedAt(): ?string;
}
