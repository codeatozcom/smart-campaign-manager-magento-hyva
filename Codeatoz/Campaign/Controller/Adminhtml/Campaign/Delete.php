<?php

declare(strict_types=1);

namespace Codeatoz\Campaign\Controller\Adminhtml\Campaign;

use Codeatoz\Campaign\Api\CampaignRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Delete extends Action
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

        if (!$campaignId) {
            $this->messageManager->addErrorMessage(__('We cannot find a campaign to delete.'));
            return $resultRedirect->setPath('*/*/');
        }

        try {
            $this->campaignRepository->deleteById($campaignId);
            $this->messageManager->addSuccessMessage(__('The campaign has been deleted.'));
        } catch (NoSuchEntityException) {
            $this->messageManager->addErrorMessage(__('This campaign no longer exists.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Something went wrong while deleting the campaign. Please review the error log.')
            );
        }

        return $resultRedirect->setPath('*/*/');
    }
}
