<?php
/*
 * Copyright (c) On Tap Networks Limited.
 */

namespace OnTap\Deal\Plugin\Framework\View\Result;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\Page;

class PagePlugin
{
    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * PagePlugin constructor.
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * @param Page $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterAddDefaultHandle(Page $subject, $result)
    {
        $action = $this->request->getFullActionName();

        if (!in_array($action, [
            'cms_index_index',
            'catalog_product_view',
            'catalog_category_view'
        ])) {
            return $result;
        }

        $page = (int) $this->request->getParam('p', 1);
        if ($page > 1) {
            $subject->addHandle('pager_not_firstpage');
        }
        return $result;
    }
}
