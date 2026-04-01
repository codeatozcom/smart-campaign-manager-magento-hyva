<?php

declare(strict_types=1);

namespace Codeatoz\Campaign\Controller\Adminhtml\Campaign;

use Codeatoz\Campaign\Api\CampaignRepositoryInterface;
use Codeatoz\Campaign\Model\CampaignFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Save extends Action
{
    public const ADMIN_RESOURCE = 'Codeatoz_Campaign::campaign_manage';

    public function __construct(
        Context $context,
        private readonly CampaignRepositoryInterface $campaignRepository,
        private readonly CampaignFactory $campaignFactory,
        private readonly DataPersistorInterface $dataPersistor
    ) {
        parent::__construct($context);
    }

    public function execute(): Redirect
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if (!$this->getRequest()->isPost()) {
            return $resultRedirect->setPath('*/*/');;
        }

        $rawPost  = $this->getRequest()->getPostValue();
        $postData = $this->flattenPostData($rawPost);

        $campaignId = isset($postData['campaign_id']) && (int) $postData['campaign_id'] > 0
            ? (int) $postData['campaign_id']
            : null;

        try {
            $campaign = $campaignId
                ? $this->campaignRepository->getById($campaignId)
                : $this->campaignFactory->create();

            $campaign->setName(trim((string) ($postData['name'] ?? '')));
            $campaign->setCampaignType((string) ($postData['campaign_type'] ?? 'general'));
            $campaign->setStartDate($this->normalizeDate((string) ($postData['start_date'] ?? '')));
            $campaign->setEndDate($this->normalizeDate((string) ($postData['end_date'] ?? '')));
            $campaign->setPriority((int) ($postData['priority'] ?? 0));

            // Auto-correct status based on dates — admin intent is respected only for Draft.
            // If dates place the campaign in the past/present/future, status is auto-set.
            $requestedStatus = (int) ($postData['status'] ?? \Codeatoz\Campaign\Model\Source\Status::DRAFT);
            $campaign->setStatus($this->resolveStatus(
                $requestedStatus,
                $campaign->getStartDate(),
                $campaign->getEndDate()
            ));
            $campaign->setStoreIds(json_encode([0]));
            $campaign->setConfigJson($this->buildConfigJson($postData));

            $this->campaignRepository->save($campaign);

            $this->messageManager->addSuccessMessage(__('The campaign has been saved successfully.'));
            $this->dataPersistor->clear('codeatoz_campaign');

            if ($this->getRequest()->getParam('back') === 'edit') {
                return $resultRedirect->setPath(
                    '*/*/edit',
                    ['campaign_id' => $campaign->getCampaignId(), '_current' => true]
                );
            }

            return $resultRedirect->setPath('*/*/');;

        } catch (NoSuchEntityException) {
            $this->messageManager->addErrorMessage(__('This campaign no longer exists.'));
            return $resultRedirect->setPath('*/*/');;

        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->dataPersistor->set('codeatoz_campaign', $postData);
            return $campaignId
                ? $resultRedirect->setPath('*/*/edit', ['campaign_id' => $campaignId])
                : $resultRedirect->setPath('*/*/new');

        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Something went wrong while saving the campaign. Please review the error log.')
            );
            $this->dataPersistor->set('codeatoz_campaign', $postData);
            return $campaignId
                ? $resultRedirect->setPath('*/*/edit', ['campaign_id' => $campaignId])
                : $resultRedirect->setPath('*/*/new');
        }
    }

    /**
     * Convert any date format to MySQL datetime (Y-m-d H:i:s).
     * The Magento date picker submits ISO 8601 (2026-03-31T14:08:00.000Z).
     * MySQL requires Y-m-d H:i:s — strtotime handles both formats.
     */
    /**
     * Determine the correct status based on requested status and campaign dates.
     *
     * Rules:
     * - Draft stays Draft regardless of dates (admin explicitly chose Draft)
     * - End date in the past → always Expired
     * - Start date in the future → Scheduled (not Active yet)
     * - Start date in the past AND end date in the future → Active
     */
    private function resolveStatus(int $requestedStatus, string $startDate, string $endDate): int
    {
        // Admin explicitly set Draft — respect that
        if ($requestedStatus === \Codeatoz\Campaign\Model\Source\Status::DRAFT) {
            return \Codeatoz\Campaign\Model\Source\Status::DRAFT;
        }

        $now   = time();
        $start = strtotime($startDate);
        $end   = strtotime($endDate);

        if ($end === false || $end < $now) {
            return \Codeatoz\Campaign\Model\Source\Status::EXPIRED;
        }

        if ($start === false || $start > $now) {
            return \Codeatoz\Campaign\Model\Source\Status::SCHEDULED;
        }

        // start <= now <= end
        return \Codeatoz\Campaign\Model\Source\Status::ACTIVE;
    }

    private function normalizeDate(string $value): string
    {
        if (empty($value)) {
            return '';
        }

        $timestamp = strtotime($value);

        if ($timestamp === false || $timestamp === -1) {
            return $value; // Let repository validation catch it
        }

        return date('Y-m-d H:i:s', $timestamp);
    }

    /**
     * UI Component forms may POST fields nested under fieldset names.
     * Flatten all nested arrays one level deep into a single key=>value map.
     */
    private function flattenPostData(array $raw): array
    {
        $flat = [];

        foreach ($raw as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $fieldKey => $fieldValue) {
                    $flat[$fieldKey] = $fieldValue;
                }
            } else {
                $flat[$key] = $value;
            }
        }

        return $flat;
    }

    private function buildConfigJson(array $postData): string
    {
        $config = [
            'promo_bar' => [
                'enabled'        => (bool) ($postData['promo_bar_enabled']        ?? false),
                'text'           => trim((string) ($postData['promo_bar_text']    ?? '')),
                'bg_color'       => $this->sanitizeColor((string) ($postData['promo_bar_bg_color']   ?? ''), '#e63946'),
                'text_color'     => $this->sanitizeColor((string) ($postData['promo_bar_text_color'] ?? ''), '#ffffff'),
                'show_countdown' => (bool) ($postData['promo_bar_show_countdown'] ?? false),
                'closable'       => (bool) ($postData['promo_bar_closable']       ?? false),
                'deal_url'       => $this->sanitizeUrl(trim((string) ($postData['promo_bar_deal_url']   ?? ''))),
                'deal_label'     => mb_substr(trim((string) ($postData['promo_bar_deal_label'] ?? 'Shop Now')), 0, 50),
            ],
            'product_badge' => [
                'enabled' => (bool) ($postData['product_badge_enabled'] ?? false),
                'text'    => mb_substr(trim((string) ($postData['product_badge_text'] ?? 'SALE')), 0, 50),
            ],
        ];

        return json_encode($config, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }

    private function sanitizeColor(string $value, string $default): string
    {
        $value = trim($value);
        return preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $value) ? $value : $default;
    }

    private function sanitizeUrl(string $value): string
    {
        if (empty($value)) {
            return '';
        }
        // Block dangerous protocols only
        if (preg_match('#^(javascript|data|vbscript)\s*:#i', $value)) {
            return '';
        }
        return $value;
    }
}
