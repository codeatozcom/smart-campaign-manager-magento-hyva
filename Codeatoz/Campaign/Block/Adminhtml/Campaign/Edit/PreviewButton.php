<?php

declare(strict_types=1);

namespace Codeatoz\Campaign\Block\Adminhtml\Campaign\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class PreviewButton implements ButtonProviderInterface
{
    public function getButtonData(): array
    {
        return [
            'label'      => __('Preview Bar'),
            'class'      => '',
            'on_click'   => 'if(typeof codeatozOpenPreview==="function"){codeatozOpenPreview();}else{setTimeout(function(){codeatozOpenPreview();},500);}; return false;',
            'sort_order' => 60,
        ];
    }
}
