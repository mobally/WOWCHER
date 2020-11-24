<?php
/**
 * Cordial/Magento Integration RFP
 *
 * @category    Cordial
 * @package     Cordial_Sync
 * @author      Cordial Team <info@cordial.com>
 * @copyright   Cordial (http://cordial.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


namespace Cordial\Sync\Model\Api;

class Email extends Client
{

    public function getTemplate($templateKey)
    {
        $path = "automationtemplates/$templateKey";

        $result = $this->_request('GET', $path);
        return $result;
    }

    /**
     * Get all automation templates name,key in Cordia
     *
     * @param int|Mage_Customer_Model_Customer $customer
     * @return boolean
     */
    public function getAllTemplates()
    {
        $path = "automationtemplates";
        $data = [
            'fields' => 'name,key',
            'page' => 1,
            'per_page' => 500
        ];

        $result = $this->_request('GET', $path, $data);
        if (!$result) {
            $result = [];
        }

        return $result;
    }

    public function createContact($emails)
    {
        $path = 'contacts';
        $data = [
            'channels' => [
                'email' => [
                    'address' => $emails,
                    'subscribeStatus' => 'subscribed' // Otherwise letters will not be sent
                ]
            ]
        ];

        return $this->_request('POST', $path, $data);
    }

    public function automationSend($templateKey, $emails, $data)
    {
        $path = "automationtemplates/$templateKey/send";

        try {
            $result = $this->_request('POST', $path, $data, true);
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
            return false;
        }

        if (!$result) {
            return false;
        }

        return true;
    }

    public function preAutomationSend($templateKey, $emails, $data)
    {
        $params = [
            'to' => [
                'contact' => [
                    'email' => $emails
                ],
                'extVars' => $data
            ]
        ];

        return $this->automationSend($templateKey, $emails, $params);
    }

    public function createTemplate($name, $code, $content)
    {
        $params = [
            'name' => $name,
            'key' => $code,
            'channel' =>'email',
            'classification' => 'transactional',
            'baseAggregation' => 'daily',
            'message' => [
                'headers' => [
                    'subject' => '{$extVars.subject}',
                    'fromEmail' => '{$extVars.fromEmail}',
                    'replyEmail' => '{$extVars.replyEmail}',
                    'fromDesc' => '{$extVars.fromDesc}'
                ],
                'content' => [
                    'text/html' => $content
                ],
            ],
        ];
        $path = 'automationtemplates';
        return $this->_request('POST', $path, $params);
    }

    public function getIncludes($key, $storeId = null)
    {
        $path = "includes/$key";
        $result = $this->_request('GET', $path);
        return $result;
    }

    public function postIncludes($key, $storeId = null)
    {
        $path = "includes";
        $options = [
            'key' => $key,
            'content' => "<!-- Content for includes $key -->"
        ];
        $result = $this->_request('POST', $path, $options);
        return $result;
    }
}
