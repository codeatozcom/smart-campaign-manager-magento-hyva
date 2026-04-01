<?php

declare(strict_types=1);

namespace Codeatoz\Campaign\Ui\DataProvider;

use Codeatoz\Campaign\Api\CampaignRepositoryInterface;
use Codeatoz\Campaign\Model\CampaignFactory;
use Codeatoz\Campaign\Model\ResourceModel\Campaign\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\DataProvider\AbstractDataProvider;

class CampaignFormDataProvider extends AbstractDataProvider
{
    private array $loadedData = [];

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        private readonly CampaignRepositoryInterface $campaignRepository,
        private readonly CampaignFactory $campaignFactory,
        private readonly DataPersistorInterface $dataPersistor,
        private readonly RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }

    public function getData(): array
    {
        if (!empty($this->loadedData)) {
            return $this->loadedData;
        }

        $campaign = $this->getCurrentCampaign();
        $data     = $campaign->getData();

        // Campaign type default
        if (empty($data['campaign_type'])) {
            $data['campaign_type'] = 'general';
        }

        // Status — cast to int for select component
        $data['status'] = isset($data['status']) ? (int) $data['status'] : 0;

        // Priority default
        if (!isset($data['priority'])) {
            $data['priority'] = 0;
        }

        $data = $this->flattenConfigJson($data);

        $this->loadedData[$campaign->getId() ?: 0] = $data;

        return $this->loadedData;
    }

    private function flattenConfigJson(array $data): array
    {
        $config = [];

        if (!empty($data['config_json'])) {
            try {
                $config = json_decode((string) $data['config_json'], true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                $config = [];
            }
        }

        $promoBar = $config['promo_bar'] ?? [];

        // Toggles must be string "0" or "1" to match valueMap xsi:type="string"
        $data['promo_bar_enabled']        = (string)(int)($promoBar['enabled']        ?? 0);
        $data['promo_bar_text']           = (string)($promoBar['text']                ?? '');
        $data['promo_bar_bg_color']       = (string)($promoBar['bg_color']            ?? '#e63946');
        $data['promo_bar_text_color']     = (string)($promoBar['text_color']          ?? '#ffffff');
        $data['promo_bar_show_countdown'] = (string)(int)($promoBar['show_countdown'] ?? 0);
        $data['promo_bar_closable']       = (string)(int)($promoBar['closable']       ?? 1);
        $data['promo_bar_deal_url']       = (string)($promoBar['deal_url']            ?? '');
        $data['promo_bar_deal_label']     = (string)($promoBar['deal_label']          ?? 'Shop Now');

        $badge = $config['product_badge'] ?? [];
        $data['product_badge_enabled'] = (string)(int)($badge['enabled'] ?? 0);
        $data['product_badge_text']    = (string)($badge['text']         ?? 'SALE');

        unset($data['config_json']);

        return $data;
    }

    private function getCurrentCampaign(): \Codeatoz\Campaign\Model\Campaign
    {
        $campaignId = (int) $this->request->getParam($this->getRequestFieldName());

        if ($campaignId) {
            try {
                return $this->campaignRepository->getById($campaignId);
            } catch (NoSuchEntityException) {
                return $this->campaignFactory->create();
            }
        }

        $persistedData = $this->dataPersistor->get('codeatoz_campaign');
        $campaign      = $this->campaignFactory->create();

        if (!empty($persistedData)) {
            $campaign->setData($persistedData);
            $this->dataPersistor->clear('codeatoz_campaign');
        }

        return $campaign;
    }
}
