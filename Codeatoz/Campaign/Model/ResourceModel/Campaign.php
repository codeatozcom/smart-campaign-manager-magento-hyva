<?php

declare(strict_types=1);

namespace Codeatoz\Campaign\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Campaign extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init('codeatoz_campaign', 'campaign_id');
    }
}
