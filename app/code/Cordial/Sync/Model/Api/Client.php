<?php

/**
 * Cordial Sync
 * © 2017 Cordial Experiences, Inc. All rights reserved.
 **/
namespace Cordial\Sync\Model\Api;

class Client
{
    /**
     * API Key
     * @var string|null
     */
    protected $apiKey = null;

    protected $storeId = null;

    protected $client = null;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Cordial\Sync\Helper\Data
     */
    protected $helper = null;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Cordial\Sync\Model\Log
     */
    protected $log;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \Cordial\Sync\Helper\Data $helper
     * @param \Cordial\Sync\Model\Log $log
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Cordial\Sync\Helper\Data $helper,
        \Cordial\Sync\Model\Log $log,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
    
        $this->helper = $helper;
        $this->log = $log;
        $this->jsonHelper = $jsonHelper;
        $this->logger = $logger;
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    public function load($storeId)
    {
        $this->storeId = $storeId;
        $this->apiKey = $this->helper->getApiKey($storeId);
        return $this;
    }

    /**
     * @return Zend_Rest_Client
     */
    protected function _getClient()
    {
        if (!$this->client instanceof \Zend_Rest_Client) {
            //$this->client = new \Zend_Rest_Client();
            $this->client = new \Zend_Http_Client();
        }
        return $this->client;
    }

    /**
     * Get API Key
     *
     * @return string
     */
    protected function _getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Get API URI
     *
     * @return string
     */
    protected function getUri()
    {
        $cordialMode = getenv('CORDIAL_MODE', true) ?: getenv('CORDIAL_MODE');
        if ($cordialMode === 'dev') {
            return Config::SCHEME . '://' . Config::ENDPOINT_DEV;
        }

        return Config::SCHEME . '://' . Config::ENDPOINT;
    }

    /**
     * @param  string $method
     * @param  string $path
     * @param  array $options
     * @param  bool $needResponse
     * @link   http://api.cordial.io/docs/v1/
     * @return Zend_Http_Response|Array|Mage_Exception
     */
    protected function _request($method = 'GET', $path = '', array $options = null, $needResponse = false)
    {
        try {
            $client = $this->_getClient();
            $username = $this->_getApiKey();
            
            if (empty($username)) {
                $username = "5fe31978e4373d1cd602c078-AWdkXWmLxlH18FZgK0n0wjpoBgnx62Ya";
            }

            $password = '';
            //$client->getHttpClient()->setAuth($username, $password, \Zend_Http_Client::AUTH_BASIC);
            $client->setAuth($username, $password, \Zend_Http_Client::AUTH_BASIC);
            $uri = $this->getUri();
            //$client->setUri($uri);

            $client->setConfig(['timeout' => Config::TIMEOUT]);
            $client->setHeaders('Accept', 'application/json');
            $client->setHeaders('Cache-Control', 'no-cache');
            $client->setHeaders('Accept-Encoding', 'gzip,deflate');
            $path = Config::VERSION . '/' . $path;

            $client->setUri($uri .'/'. $path);

            switch ($method) {
                case 'POST':
                    //$response = $client->restPost($path, $options);
                    $client->setParameterPost($options);
                    $response = $client->request('POST');
                    break;

                case 'GET';
                    //$response = $client->restGet($path, $options);
		    $client->setParameterGet($options);
                    $response = $client->request('GET');
                    break;

                case 'PUT';
                    //$response = $client->restPut($path, $options);
                    $client->setParameterPost($options);
                    $response = $client->request('PUT');
                    break;

                case 'DELETE';
                    $client->setParameterPost($options);
                    $response = $client->request('DELETE');
                    break;

                default:
                    $this->logger->error('Wrong REST method ' . $method);
            }

            $this->_save($method, $path, $options, $response);
        } catch (\Zend_Http_Client_Adapter_Exception $e) {
            $this->_save($method, $path, $options, $e);
            throw new \Magento\Framework\Exception\LocalizedException(__('Unable to Connect to Cordial API'));
        }

        if ($response->isError()) {
            if ($response->getStatus() == '401' && $response->getMessage() == 'Unauthorized') {
                $this->logger->error("Cordial 401 err: {$method} {$uri}/{$path} " . $response->getMessage());
                $this->logger->error(var_export($options, true) . " :: {$username}:{$password}");
                throw new \Magento\Framework\Exception\LocalizedException(__('Unable to Connect 401 to Cordial API'));
            }
            return false;
        }

        if ($needResponse) {
            return $this->jsonHelper->jsonDecode($response->getBody());
        }

        if ($method == 'GET') {
            return $this->jsonHelper->jsonDecode($response->getBody());
        }
        return true;
    }

    /**
     * Save log
     */
    protected function _save($method = 'GET', $path = '', array $options = null, $response)
    {
        $data = [];
        $data['method']     = $method;
        $data['path']       = $path;
        $data['options']    = $this->jsonHelper->jsonEncode($options);
        $data['message']    = $response->getMessage();

        if ($response instanceof \Zend_Http_Response) {
            $data['body'] = $response->getBody();
            $data['code'] = $response->getStatus();
        }

        $log = $this->log->setData($data);

        try {
            $log->save();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return;
    }
}
