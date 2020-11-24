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

namespace Cordial\Sync\Model;

use Zend\Mail\Message;
use Zend\Mail\Address;
use Zend\Mail\AddressList;

class Transport extends \Zend_Mail_Transport_Sendmail implements \Magento\Framework\Mail\TransportInterface
{
    /**
     * @var \Magento\Framework\Mail\MessageInterface
     */
    protected $message;

    /**
     * Cordial Helper class
     *
     * @var \Cordial\Sync\Helper\Data
     */
    protected $helper;

    /**
     * @var \Cordial\Sync\Model\Template
     */
    protected $template;

    /**
     * @var \Cordial\Sync\Model\Api\Email
     */
    protected $api;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Transport constructor.
     * @param \Magento\Framework\Mail\MessageInterface $message
     * @param \Cordial\Sync\Helper\Data $helper
     * @param Template $template
     * @param Api\Email $api
     * @param \Magento\Framework\Mail\Template\SenderResolverInterface $senderResolver
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\Mail\EmailMessageInterface $message,
        \Cordial\Sync\Helper\Data $helper,
        \Cordial\Sync\Model\Template $template,
        \Cordial\Sync\Model\Api\Email $api,
        \Magento\Framework\Mail\Template\SenderResolverInterface $senderResolver,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->message = $message;
        $this->helper = $helper;
        $this->template = $template;
        $this->api = $api;
        $this->senderResolver = $senderResolver;
        $this->logger = $logger;
    }
    /**
     * @see parent
     */
    public function sendMessage()
    {
            $templateVariables = $this->helper->getParsedCordialVariables();
        //$storeId = $this->helper->getStoreId($this->_message);
            if (isset($templateVariables['storeId'])) {
                $storeId = $templateVariables['storeId'];


            if (is_a($this->message, '\Magento\Framework\Mail\EmailMessageInterface')) {
                $zendMessage = Message::fromString($this->message->getRawMessage());
                $body = $zendMessage->getBodyText();
                $fromArray = $this->getEmailsFromAddressList($zendMessage->getFrom());
                $from = null;
                if (count($fromArray)) {
                    $from = array_pop($fromArray);
                } else {
                    // Falls back to general email address. see https://github.com/magento/magento2/issues/14952
                    $from = $this->senderResolver->resolve('general', $storeId);
                    $this->logger->debug("Fell back to general email address for 'From'.  See https://github.com/magento/magento2/issues/14952");
                }
                $recipients = array_merge(
                    $this->getEmailsFromAddressList($zendMessage->getTo()),
                    $this->getEmailsFromAddressList($zendMessage->getCc()),
                    $this->getEmailsFromAddressList($zendMessage->getBcc())
                );
                $reply = $this->getEmailsFromAddressList($zendMessage->getReplyTo());
                $subject = $zendMessage->getSubject();
            } else {
                $body = $this->message->getBody()->getRawContent();
                $from = $this->message->getFrom();
                $recipients = $this->message->getRecipients();
                $reply = $this->message->getReplyTo();
                $subject =  $this->message->getSubject();
            }

            if ($body !== 'nosend') {

                $this->logger->debug('Generating message delivery for ' . implode(', ', $recipients)
                    . "\nBacktrace: \n\t" . $this->helper->getSimplifiedBacktrace());

                $temlateId = $templateVariables['templateIdentifier'];
                unset($templateVariables['templateIdentifier']);
                $designParams = $templateVariables['designParams'];

                $header = [
                    'subject' => $subject,
                    'fromEmail' => $from,
                ];
                if (!isset($reply) || empty($reply)) {
                    $header['replyEmail'] = $header['fromEmail'];;
                } else {
                    $header['replyEmail'] = $reply;
                }

                $templateVariables = array_merge($header, $templateVariables);
                $temlateIdTheme = $temlateId . '/'. $designParams['theme'];
                $templateCordial = $this->template->loadByOrigCode($temlateIdTheme, $storeId);
                if (!$templateCordial) {
                    $templateCordial = $this->template->loadByOrigCode($temlateId, $storeId);
                }

                if ($templateCordial && !is_null($templateCordial['template_code'])) {
                    $api = $this->api->load($storeId);
                    foreach ($recipients as $recipient) {
                        $api->preAutomationSend($templateCordial['template_code'], $recipient, $templateVariables);
                    }
                }

            }
        }
        }

    /**
     * @see \Magento\Framework\Mail\TransportInterface::getMessage()
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Convenience function for pulling the email address string from an AddressList
     * @see \Zend\Mail\AddressList
     *
     * @param AddressList $addressList
     * @return String[]
     */
    protected function getEmailsFromAddressList(AddressList $addressList)
    {
        $emails = [];
        foreach ($addressList as $address) {
            $emails[] = $address->getEmail();
        }
        return $emails;
    }
}
