<?php

declare(strict_types=1);

namespace Codeatoz\Campaign\Block\Adminhtml\Campaign\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class EnableButton implements ButtonProviderInterface
{
    public function __construct(
        private readonly Context $context
    ) {
    }

    public function getButtonData(): array
    {
        $campaignId = (int) $this->context->getRequest()->getParam('campaign_id');

        if (!$campaignId) {
            return [];
        }

        return [
            'label'      => __('Enable'),
            'class'      => 'save',
            'on_click'   => sprintf(
                "location.href = '%s';",
                $this->context->getUrlBuilder()->getUrl(
                    'codeatoz_campaign/campaign/toggleStatus',
                    ['campaign_id' => $campaignId, 'status' => 1]
                )
            ),
            'sort_order' => 40,
        ];
    }
}
