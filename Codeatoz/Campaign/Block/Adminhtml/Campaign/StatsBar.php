<?php

declare(strict_types=1);

namespace Codeatoz\Campaign\Block\Adminhtml\Campaign;

use Codeatoz\Campaign\Model\ResourceModel\Campaign\CollectionFactory;
use Codeatoz\Campaign\Model\Source\Status;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;

class StatsBar extends Template
{
    protected $_template = 'Codeatoz_Campaign::adminhtml/stats_bar.phtml';

    private const CAMPAIGN_LIMIT = 3;

    public function __construct(
        Context $context,
        private readonly CollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getActiveCount(): int
    {
        return $this->countByStatus(Status::ACTIVE);
    }

    public function getScheduledCount(): int
    {
        return $this->countByStatus(Status::SCHEDULED);
    }

    public function getDraftCount(): int
    {
        return $this->countByStatus(Status::DRAFT);
    }

    public function getExpiredCount(): int
    {
        return $this->countByStatus(Status::EXPIRED);
    }

    public function getTotalCount(): int
    {
        return (int) $this->collectionFactory->create()->getSize();
    }

    public function getCampaignLimit(): int
    {
        return self::CAMPAIGN_LIMIT;
    }

    public function getCreateUrl(): string
    {
        return $this->getUrl('codeatoz_campaign/campaign/new');
    }

    private function countByStatus(int $status): int
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('status', ['eq' => $status]);
        return (int) $collection->getSize();
    }
}
