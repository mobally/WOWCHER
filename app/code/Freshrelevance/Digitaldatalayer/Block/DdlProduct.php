<?php

namespace Freshrelevance\Digitaldatalayer\Block;

use Exception;

/**
 * Class DdlProduct
 * @package Freshrelevance\Digitaldatalayer\Block
 */
class DdlProduct extends AbstractBlock
{
    /**
     * Get transactional data for default and checkout views
     * @return string
     */
    public function getTransactionalData()
    {
        try {
            if ($this->pageTypeHelper->isTransactionDataAvailable()) {
                $ddlData = $this->dataHelper->getDdlTransactionData();
            } else {
                $ddlData = $this->dataHelper->getDdlCmsData();
            }
            return $ddlData;
        } catch (Exception $exception) {
            $this->_logger->error($exception->getMessage());
            return "{}";
        }
    }

    /**
     * Get ddl data for the specific block
     * @return string
     */
    public function getDDLData()
    {
        $data = "{}";

        if ($product = $this->getProduct()) {
            try {
                $data = $this->dataHelper->getDdlProductData($product);
            } catch (Exception $exception) {
                $this->_logger->error($exception->getMessage());
                return "{}";
            }
        }

        return $data;
    }
}
