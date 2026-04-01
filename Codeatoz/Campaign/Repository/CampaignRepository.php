<?php

declare(strict_types=1);

namespace Codeatoz\Campaign\Repository;

use Codeatoz\Campaign\Api\CampaignRepositoryInterface;
use Codeatoz\Campaign\Api\Data\CampaignInterface;
use Codeatoz\Campaign\Model\CampaignFactory;
use Codeatoz\Campaign\Model\ResourceModel\Campaign as CampaignResource;
use Codeatoz\Campaign\Model\ResourceModel\Campaign\Collection;
use Codeatoz\Campaign\Model\ResourceModel\Campaign\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class CampaignRepository implements CampaignRepositoryInterface
{
    private const CAMPAIGN_LIMIT = 3;

    public function __construct(
        private readonly CampaignFactory $campaignFactory,
        private readonly CampaignResource $campaignResource,
        private readonly CollectionFactory $collectionFactory,
        private readonly SearchResultsInterfaceFactory $searchResultsFactory,
        private readonly CollectionProcessorInterface $collectionProcessor
    ) {
    }

    /**
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    public function save(CampaignInterface $campaign): CampaignInterface
    {
        $this->validateCampaign($campaign);

        // Enforce free edition limit on new campaigns
        if (!$campaign->getCampaignId()) {
            $collection = $this->collectionFactory->create();
            if ($collection->getSize() >= self::CAMPAIGN_LIMIT) {
                throw new LocalizedException(
                    __('Campaign limit reached. Free edition allows a maximum of %1 campaigns.', self::CAMPAIGN_LIMIT)
                );
            }
        }

        try {
            $this->campaignResource->save($campaign);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __('Could not save the campaign: %1', $e->getMessage()),
                $e
            );
        }

        return $campaign;
    }

    /**
     * @throws NoSuchEntityException
     */
    public function getById(int $campaignId): CampaignInterface
    {
        $campaign = $this->campaignFactory->create();
        $this->campaignResource->load($campaign, $campaignId);

        if (!$campaign->getCampaignId()) {
            throw new NoSuchEntityException(
                __('Campaign with ID "%1" does not exist.', $campaignId)
            );
        }

        return $campaign;
    }

    /**
     * @throws CouldNotDeleteException
     */
    public function delete(CampaignInterface $campaign): bool
    {
        try {
            $this->campaignResource->delete($campaign);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(
                __('Could not delete the campaign: %1', $e->getMessage()),
                $e
            );
        }

        return true;
    }

    /**
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById(int $campaignId): bool
    {
        return $this->delete($this->getById($campaignId));
    }

    /**
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var SearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * Return the highest-priority enabled campaign currently within its schedule.
     */
    public function getActiveCampaignForStore(int $storeId): ?CampaignInterface
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();

        $now = (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');

        $collection->addFieldToFilter('status', ['eq' => \Codeatoz\Campaign\Model\Source\Status::ACTIVE]);
        $collection->addFieldToFilter('start_date', ['lteq' => $now]);
        $collection->addFieldToFilter('end_date', ['gteq' => $now]);
        $collection->setOrder('priority', Collection::SORT_ORDER_DESC);
        $collection->setPageSize(1);
        $collection->setCurPage(1);

        /** @var CampaignInterface|false $campaign */
        $campaign = $collection->getFirstItem();

        if (!$campaign || !$campaign->getCampaignId()) {
            return null;
        }

        return $campaign;
    }

    /**
     * Validate campaign data before persisting.
     *
     * @throws LocalizedException
     */
    private function validateCampaign(CampaignInterface $campaign): void
    {
        if (empty(trim($campaign->getName()))) {
            throw new LocalizedException(__('Campaign name is required.'));
        }

        if (strlen($campaign->getName()) > 255) {
            throw new LocalizedException(__('Campaign name must not exceed 255 characters.'));
        }

        if (empty($campaign->getStartDate())) {
            throw new LocalizedException(__('Start date is required.'));
        }

        if (empty($campaign->getEndDate())) {
            throw new LocalizedException(__('End date is required.'));
        }

        $start = strtotime($campaign->getStartDate());
        $end   = strtotime($campaign->getEndDate());

        if ($start === false || $start === -1) {
            throw new LocalizedException(__('Start date is not a valid datetime.'));
        }

        if ($end === false || $end === -1) {
            throw new LocalizedException(__('End date is not a valid datetime.'));
        }

        if ($end <= $start) {
            throw new LocalizedException(__('End date must be after start date.'));
        }

        $configJson = $campaign->getConfigJson();
        if (!empty($configJson)) {
            try {
                json_decode($configJson, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                throw new LocalizedException(
                    __('Campaign configuration contains invalid JSON: %1', $e->getMessage())
                );
            }
        }
    }
}
