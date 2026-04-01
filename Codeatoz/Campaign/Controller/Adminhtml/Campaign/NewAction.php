<?php

declare(strict_types=1);

namespace Codeatoz\Campaign\Controller\Adminhtml\Campaign;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\ForwardFactory;

class NewAction extends Action
{
    public const ADMIN_RESOURCE = 'Codeatoz_Campaign::campaign_manage';

    public function __construct(
        Context $context,
        private readonly ForwardFactory $resultForwardFactory
    ) {
        parent::__construct($context);
    }

    public function execute(): Forward
    {
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('edit');
    }
}
