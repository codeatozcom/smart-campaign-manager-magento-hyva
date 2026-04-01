<?php

declare(strict_types=1);

namespace Codeatoz\Campaign\Controller\Adminhtml\Campaign;

use Codeatoz\Campaign\Api\CampaignRepositoryInterface;
use Codeatoz\Campaign\Model\CampaignFactory;
use Codeatoz\Campaign\Model\Source\Status;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Duplicate extends Action
{
    public const ADMIN_RESOURCE = 'Codeatoz_Campaign::campaign_manage';

    public function __construct(
        Context $context,
        private readonly CampaignRepositoryInterface $campaignRepository,
        private readonly CampaignFactory $campaignFactory
    ) {
        parent::__construct($context);
    }

    public function execute(): Redirect
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $campaignId     = (int) $this->getRequest()->getParam('campaign_id');

        if (!$campaignId) {
            $this->messageManager->addErrorMessage(__('Invalid campaign.'));
            return $resultRedirect->setPath('*/*/');;
        }

        try {
            $original  = $this->campaignRepository->getById($campaignId);
            $duplicate = $this->campaignFactory->create();

            $duplicate->setName(__('Copy of %1', $original->getName())->__toString());
            $duplicate->setCampaignType($original->getCampaignType());
            $duplicate->setStatus(Status::DRAFT);
            $duplicate->setStartDate($original->getStartDate());
            $duplicate->setEndDate($original->getEndDate());
            $duplicate->setPriority($original->getPriority());
            $duplicate->setStoreIds($original->getStoreIds());
            $duplicate->setConfigJson($original->getConfigJson());

            $saved = $this->campaignRepository->save($duplicate);

            $this->messageManager->addSuccessMessage(
                __('Campaign "%1" has been duplicated. Review and activate when ready.', $original->getName())
            );

            return $resultRedirect->setPath('*/*/edit', ['campaign_id' => $saved->getCampaignId()]);

        } catch (NoSuchEntityException) {
            $this->messageManager->addErrorMessage(__('This campaign no longer exists.'));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Could not duplicate the campaign.'));
        }

        return $resultRedirect->setPath('*/*/');;
    }
}
