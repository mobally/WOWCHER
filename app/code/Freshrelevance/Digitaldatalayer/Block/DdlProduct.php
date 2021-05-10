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
     private function specialchars($string) {
        return addslashes($string);
    }
    
    private function getEol($htmlOutput = 0) {
        $eol = PHP_EOL;
        if ($htmlOutput == 1) {
            $eol = "<br />";
        }
        return $eol;
    }
    
    public function getProductDetailsFbJs($html = 0) {
			
			$product = $this->getProduct();
			$fbSku = $this->specialchars($product->getSku());
			$fbName = $this->specialchars($product->getName());
			$fbPrice = round($product->getFinalPrice(), 2);
            $eol = ($this->getEol($html));
               $productId = $product->getId();
			    $productName = $this->specialchars($product->getName());
						
					$fbProduct =	"fbq('track', 'ViewContent', { 
							content_type: 'product',
							content_ids: ['$fbSku'],
							content_name: '$fbName'
							
							});
						" .$eol;
						
    
	
        
        return $fbProduct;
            
    }
    
}
