<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */

namespace OnTap\ProductImport\Controller\Adminhtml\Deal;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use OnTap\ProductImport\Model\Importer;

class Import extends Action implements HttpPostActionInterface
{
    /**
     * @var Importer
     */
    protected Importer $importer;

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'OnTap_ProductImport::import';

    /**
     * Import constructor.
     * @param Context $context
     * @param Importer $importer
     */
    public function __construct(Context $context, Importer $importer)
    {
        parent::__construct($context);
        $this->importer = $importer;
    }

    /**
     * {@inheritDoc}
     */
    public function execute()
    {
        $dealId = $this->getRequest()->getParam('deal_id');

        if (!is_numeric($dealId)) {
            $this->messageManager->addErrorMessage('Invalid DEAL ID provided');
            return $this->_redirect('productimport/deal/index');
        }

        try {
            $url = sprintf('https://public-api.wowcher.co.uk/europe/deal/%s', $dealId);
            //$url = sprintf('https://public-api.wowcher.co.uk/v1/deal/%s', $dealId);
            $this->importer->importFromUrl($url);
            $this->messageManager->addSuccessMessage("Import successful");
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e);
        }

        return $this->_redirect('productimport/deal/index');
    }
}
