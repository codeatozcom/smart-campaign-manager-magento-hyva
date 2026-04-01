<?php

declare(strict_types=1);

namespace Codeatoz\Campaign\Block\Adminhtml\Campaign;

use Codeatoz\Campaign\Model\ResourceModel\Campaign\CollectionFactory;
use Codeatoz\Campaign\Model\Source\Status;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;

class ConflictNotice extends Template
{
    protected $_template = 'Codeatoz_Campaign::adminhtml/conflict_notice.phtml';

    public function __construct(
        Context $context,
        private readonly CollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Return all Active campaigns whose time windows overlap right now.
     * Conflict = more than 1 Active campaign at the same time.
     */
    public function getConflictingCampaigns(): array
    {
        $now = (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');

        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('status', ['eq' => Status::ACTIVE]);
        $collection->addFieldToFilter('start_date', ['lteq' => $now]);
        $collection->addFieldToFilter('end_date',   ['gteq' => $now]);
        $collection->setOrder('priority', 'DESC');

        $items = $collection->getItems();

        if (count($items) <= 1) {
            return [];
        }

        return array_values($items);
    }

    /**
     * Return upcoming Scheduled campaigns (start_date in the future).
     */
    public function getUpcomingCampaigns(): array
    {
        $now = (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');

        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('status', ['eq' => Status::SCHEDULED]);
        $collection->addFieldToFilter('start_date', ['gt' => $now]);
        $collection->setOrder('start_date', 'ASC');

        return array_values($collection->getItems());
    }

    public function getEditUrl(int $campaignId): string
    {
        return $this->getUrl('codeatoz_campaign/campaign/edit', ['campaign_id' => $campaignId]);
    }

    public function formatCampaignDate(string $date): string
    {
        try {
            $dt = new \DateTime($date, new \DateTimeZone('UTC'));
            return $dt->format('M j, Y g:i A') . ' UTC';
        } catch (\Exception $e) {
            return $date;
        }
    }
}
