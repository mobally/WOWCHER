<?php

namespace Cordial\Sync\Plugin;

class TransportInterfaceFactory
{
    /**
     * Cordial Transport Factory
     *
     * @var \Cordial\Sync\Model\TransportFactory
     */
    protected $cordialTransportFactory;

    /**
     * Cordial Helper class
     *
     * @var \Cordial\Sync\Helper\Data
     */
    protected $cordialHelper;

    /**
     * Cordial constructor.
     * @param \Cordial\Sync\Helper\Data $cordialHelper
     * @param \Cordial\Sync\Model\TransportFactory $cordialTransportFactory
     */
    public function __construct(
        \Cordial\Sync\Helper\Data $cordialHelper,
        \Cordial\Sync\Model\TransportFactory $cordialTransportFactory
    ) {
        $this->cordialHelper = $cordialHelper;
        $this->cordialTransportFactory = $cordialTransportFactory;
    }

    /**
     * Replace mail transport with Mandrill if needed
     *
     * @param \Magento\Framework\Mail\TransportInterfaceFactory $subject
     * @param \Closure $proceed
     * @param array $data
     *
     * @return \Magento\Framework\Mail\TransportInterface
     */
    public function aroundCreate(
        \Magento\Framework\Mail\TransportInterfaceFactory $subject,
        \Closure $proceed,
        array $data = []
    ) {
        if ($this->isRouteEnabled() === false) {
            /** @var \Magento\Framework\Mail\TransportInterface $transport */
            return $proceed($data);
        } else {
            $this->cordialHelper->debug('Cordial Email');
            return $this->cordialTransportFactory->create($data);
        }
    }

    /**
     * Get status of Mandrill
     *
     * @return bool
     */
    private function isRouteEnabled()
    {
        return $this->cordialHelper->routeEmail();
    }
}
