<?php

declare(strict_types=1);

namespace Codeatoz\Campaign\Api;

use Codeatoz\Campaign\Api\Data\CampaignInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

interface CampaignRepositoryInterface
{
    /**
     * Save campaign.
     *
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    public function save(CampaignInterface $campaign): CampaignInterface;

    /**
     * Load campaign by ID.
     *
     * @throws NoSuchEntityException
     */
    public function getById(int $campaignId): CampaignInterface;

    /**
     * Delete campaign.
     *
     * @throws CouldNotDeleteException
     */
    public function delete(CampaignInterface $campaign): bool;

    /**
     * Delete campaign by ID.
     *
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById(int $campaignId): bool;

    /**
     * Get list of campaigns by search criteria.
     *
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface;

    /**
     * Get the active campaign for a specific store based on current datetime and priority.
     */
    public function getActiveCampaignForStore(int $storeId): ?CampaignInterface;
}
