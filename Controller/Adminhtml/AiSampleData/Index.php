<?php

declare(strict_types=1);

namespace Opengento\SampleAiData\Controller\Adminhtml\AiSampleData;

use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action as BackendAction;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\LayoutFactory;

class Index extends BackendAction
{
    public function __construct(
        private readonly PageFactory $resultPageFactory,
        Context $context,
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        $resultPage->setActiveMenu('Opengento_SampleAiData::generate');

        return $resultPage;
    }
}
