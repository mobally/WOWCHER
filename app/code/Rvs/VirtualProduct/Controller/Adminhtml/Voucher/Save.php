<?php

namespace Rvs\VirtualProduct\Controller\Adminhtml\Voucher;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Exception\LocalizedException;

class Save extends \Rvs\VirtualProduct\Controller\Adminhtml\Voucher implements HttpPostActionInterface
{
    /**
     * @return void
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            /** @var $session \Magento\Backend\Model\Session */
            $session = $this->_objectManager->get(\Magento\Backend\Model\Session::class);
            try {
                $model = $this->_getVoucher();

                $sku        = $this->getRequest()->getParam('sku', $model->getSku());
                $childSku   = $this->getRequest()->getParam('child_sku', $model->getChildSku());

                $model->setVoucherCode($this->getRequest()->getParam('voucher_code', $model->getVoucherCode()))
                    ->setStatus($this->getRequest()->getParam('status', $model->getStatus()))
                    ->setExpirationDate($this->getRequest()->getParam('expiration_date', $model->getExpirationDate()))
                    ->setUrl($this->getRequest()->getParam('url'),$model->getUrl());

                if($sku && $childSku){
                    $model->setSku($sku);
                    $model->setChildSku($childSku);
                    $model->setFinalSku($sku.'-'.$childSku);
                }else{
                    $model->setSku($sku);
                    $model->setFinalSku($sku);
                }
                
                $model->save();

                $this->messageManager->addSuccess(__('The Voucher has been saved.'));
                $this->_redirect('*/*/');
                return;
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $session->setVoucherData($data);
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __('An error occurred while saving the Voucher. Please try to save again.')
                );
                $session->setVoucherData($data);
            }
        }
        $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl($this->getUrl('*')));
    }
}
