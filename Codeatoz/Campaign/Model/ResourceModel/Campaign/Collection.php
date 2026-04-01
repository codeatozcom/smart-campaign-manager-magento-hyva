<?php

declare(strict_types=1);

namespace Codeatoz\Campaign\Model\ResourceModel\Campaign;

use Codeatoz\Campaign\Model\Campaign;
use Codeatoz\Campaign\Model\ResourceModel\Campaign as CampaignResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'campaign_id';

    protected function _construct(): void
    {
        $this->_init(Campaign::class, CampaignResource::class);
    }
}
