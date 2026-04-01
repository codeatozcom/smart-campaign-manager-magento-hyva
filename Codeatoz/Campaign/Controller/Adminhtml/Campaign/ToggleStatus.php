<?php

declare(strict_types=1);

namespace Codeatoz\Campaign\Controller\Adminhtml\Campaign;

use Codeatoz\Campaign\Api\CampaignRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class ToggleStatus extends Action
{
    public const ADMIN_RESOURCE = 'Codeatoz_Campaign::campaign_manage';

    public function __construct(
        Context $context,
        private readonly CampaignRepositoryInterface $campaignRepository
    ) {
        parent::__construct($context);
    }

    public function execute(): Redirect
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $campaignId     = (int) $this->getRequest()->getParam('campaign_id');
        $newStatus      = (int) $this->getRequest()->getParam('status');

        if (!$campaignId) {
            $this->messageManager->addErrorMessage(__('Invalid campaign.'));
            return $resultRedirect->setPath('*/*/');;
        }

        // Only allow 0 or 1
        $newStatus = $newStatus > 0 ? 1 : 0;

        try {
            $campaign = $this->campaignRepository->getById($campaignId);
            $campaign->setStatus($newStatus);
            $this->campaignRepository->save($campaign);

            $label = $newStatus === 1 ? __('enabled') : __('disabled');
            $this->messageManager->addSuccessMessage(
                __('Campaign "%1" has been %2.', $campaign->getName(), $label)
            );
        } catch (NoSuchEntityException) {
            $this->messageManager->addErrorMessage(__('This campaign no longer exists.'));
            return $resultRedirect->setPath('*/*/');;
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Something went wrong while updating the campaign status.')
            );
        }

        return $resultRedirect->setPath(
            '*/*/edit',
            ['campaign_id' => $campaignId]
        );
    }
}
