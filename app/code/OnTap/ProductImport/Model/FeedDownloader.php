<?php declare(strict_types=1);
/*
 * Copyright (c) On Tap Networks Limited.
 */
namespace OnTap\ProductImport\Model;

use Magento\Framework\HTTP\Adapter\CurlFactory;

class FeedDownloader
{
    /**
     * @var CurlFactory
     */
    protected CurlFactory $curlFactory;

    /**
     * FeedDownloader constructor.
     * @param CurlFactory $curlFactory
     */
    public function __construct(CurlFactory $curlFactory)
    {
        $this->curlFactory = $curlFactory;
    }

    /**
     * Retrieve feed data
     *
     * @param string $url
     * @return string
     */
    public function fetchData(string $url): string
    {
        $curl = $this->curlFactory->create();
        $curl->setConfig(
            [
                'timeout'   => 10
            ]
        );
        $curl->write(\Zend_Http_Client::GET, $url, '1.1');

        $data = $curl->read();
        $data = preg_split('/^\r?$/m', $data, 2);
        $data = trim($data[1]);
        $curl->close();

        return $data;
    }
}
