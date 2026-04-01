<?php

declare(strict_types=1);

namespace Codeatoz\Campaign\Controller\Adminhtml\Campaign;

use Codeatoz\Campaign\Api\CampaignRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Edit extends Action
{
    public const ADMIN_RESOURCE = 'Codeatoz_Campaign::campaign_manage';

    public function __construct(
        Context $context,
        private readonly PageFactory $resultPageFactory,
        private readonly CampaignRepositoryInterface $campaignRepository
    ) {
        parent::__construct($context);
    }

    public function execute(): Page|Redirect
    {
        $campaignId = (int) $this->getRequest()->getParam('campaign_id');

        if ($campaignId) {
            try {
                $campaign = $this->campaignRepository->getById($campaignId);
                $title    = __('Edit Campaign: %1', $campaign->getName());
            } catch (NoSuchEntityException) {
                $this->messageManager->addErrorMessage(__('This campaign no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        } else {
            $title = __('New Campaign');
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Codeatoz_Campaign::campaign_list');
        $resultPage->getConfig()->getTitle()->prepend(__('Smart Campaigns'));
        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }
}
