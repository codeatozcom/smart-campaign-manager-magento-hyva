<?php

declare(strict_types=1);

namespace Codeatoz\Campaign\Ui\Component\Listing\Column;

use Codeatoz\Campaign\Model\Source\Status;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class StatusBadge extends Column
{
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            $status = (int) ($item['status'] ?? Status::DRAFT);

            [$bgColor, $textColor, $borderColor, $dotColor, $label] = $this->getStyleValues($status);

            $style = 'display:inline-flex;align-items:center;gap:5px;'
                . 'padding:3px 10px;border-radius:12px;font-size:12px;font-weight:600;'
                . 'white-space:nowrap;background:' . $bgColor . ';color:' . $textColor . ';'
                . 'border:1px solid ' . $borderColor . ';';

            $dotStyle = 'width:7px;height:7px;border-radius:50%;'
                . 'background:' . $dotColor . ';flex-shrink:0;';

            $safeLabel = htmlspecialchars((string) __($label), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            $item[$this->getData('name')] =
                '<span style="' . $style . '">'
                . '<span style="' . $dotStyle . '"></span>'
                . $safeLabel
                . '</span>';
        }

        return $dataSource;
    }

    private function getStyleValues(int $status): array
    {
        return match ($status) {
            Status::SCHEDULED => ['#e8f0fe', '#1a73e8', '#aecbfa', '#1a73e8', 'Scheduled'],
            Status::ACTIVE    => ['#e6f4ea', '#137333', '#81c995', '#137333', 'Active'],
            Status::EXPIRED   => ['#fce8e6', '#c5221f', '#f28b82', '#c5221f', 'Expired'],
            default           => ['#f1f3f4', '#5f6368', '#dadce0', '#9aa0a6', 'Draft'],
        };
    }
}
