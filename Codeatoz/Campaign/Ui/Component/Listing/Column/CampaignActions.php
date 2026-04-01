<?php

declare(strict_types=1);

namespace Codeatoz\Campaign\Ui\Component\Listing\Column;

use Codeatoz\Campaign\Model\Source\Status;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class CampaignActions extends Column
{
    private const URL_PATH_EDIT        = 'codeatoz_campaign/campaign/edit';
    private const URL_PATH_DELETE      = 'codeatoz_campaign/campaign/delete';
    private const URL_PATH_DUPLICATE   = 'codeatoz_campaign/campaign/duplicate';
    private const URL_PATH_TOGGLE      = 'codeatoz_campaign/campaign/toggleStatus';

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        private readonly UrlInterface $urlBuilder,
        private readonly Escaper $escaper,
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
            if (!isset($item['campaign_id'])) {
                continue;
            }

            $campaignName = $this->escaper->escapeHtml($item['name'] ?? '');
            $campaignId   = (int) $item['campaign_id'];
            $status       = (int) ($item['status'] ?? Status::DRAFT);

            $actions = [];

            // Edit
            $actions['edit'] = [
                'href'  => $this->urlBuilder->getUrl(self::URL_PATH_EDIT, ['campaign_id' => $campaignId]),
                'label' => __('Edit'),
            ];

            // Duplicate
            $actions['duplicate'] = [
                'href'  => $this->urlBuilder->getUrl(self::URL_PATH_DUPLICATE, ['campaign_id' => $campaignId]),
                'label' => __('Duplicate'),
            ];

            // Deactivate — only shown for Active campaigns
            if ($status === Status::ACTIVE) {
                $actions['deactivate'] = [
                    'href'    => $this->urlBuilder->getUrl(
                        self::URL_PATH_TOGGLE,
                        ['campaign_id' => $campaignId, 'status' => Status::DRAFT]
                    ),
                    'label'   => __('Deactivate'),
                    'confirm' => [
                        'title'   => __('Deactivate Campaign'),
                        'message' => __('Move "%1" back to Draft?', $campaignName),
                    ],
                ];
            }

            // Activate — shown for Draft and Scheduled campaigns
            if (in_array($status, [Status::DRAFT, Status::SCHEDULED], true)) {
                $actions['activate'] = [
                    'href'  => $this->urlBuilder->getUrl(
                        self::URL_PATH_TOGGLE,
                        ['campaign_id' => $campaignId, 'status' => Status::SCHEDULED]
                    ),
                    'label' => __('Schedule'),
                ];
            }

            // Delete
            $actions['delete'] = [
                'href'    => $this->urlBuilder->getUrl(self::URL_PATH_DELETE, ['campaign_id' => $campaignId]),
                'label'   => __('Delete'),
                'confirm' => [
                    'title'   => __('Delete Campaign'),
                    'message' => __('Are you sure you want to delete "%1"?', $campaignName),
                ],
                'post' => true,
            ];

            $item[$this->getData('name')] = $actions;
        }

        return $dataSource;
    }
}
