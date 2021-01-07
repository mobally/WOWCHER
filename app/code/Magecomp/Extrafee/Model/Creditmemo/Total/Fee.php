<?php

namespace Magecomp\Extrafee\Model\Creditmemo\Total;

use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;

class Fee extends AbstractTotal
{
    /**
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $creditmemo->setFee(0);
        
        $amount = $creditmemo->getOrder()->getFee();
        $creditmemo->setFee($amount);

        $creditmemo->setGrandTotal($creditmemo->getGrandTotal());
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal());

        return $this;
    }
}
