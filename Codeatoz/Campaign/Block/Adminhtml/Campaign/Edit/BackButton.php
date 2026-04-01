<?php

declare(strict_types=1);

namespace Codeatoz\Campaign\Block\Adminhtml\Campaign\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Backend\Block\Widget\Context;

class BackButton implements ButtonProviderInterface
{
    public function __construct(
        private readonly Context $context
    ) {
    }

    public function getButtonData(): array
    {
        return [
            'label'      => __('Back'),
            'on_click'   => sprintf("location.href = '%s';", $this->getBackUrl()),
            'class'      => 'back',
            'sort_order' => 10,
        ];
    }

    private function getBackUrl(): string
    {
        return $this->context->getUrlBuilder()->getUrl('*/*/');
    }
}
