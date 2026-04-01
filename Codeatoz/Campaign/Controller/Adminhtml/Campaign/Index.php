<?php

declare(strict_types=1);

namespace Codeatoz\Campaign\Controller\Adminhtml\Campaign;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    public const ADMIN_RESOURCE = 'Codeatoz_Campaign::campaign_manage';

    public function __construct(
        Context $context,
        private readonly PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
    }

    public function execute(): Page
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Codeatoz_Campaign::campaign_list');
        $resultPage->getConfig()->getTitle()->prepend(__('Smart Campaigns'));

        return $resultPage;
    }
}
