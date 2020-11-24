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

namespace Cordial\Sync\Model\System;

use Magento\Framework\Data\OptionSourceInterface;

class Template extends \Magento\Framework\DataObject implements OptionSourceInterface
{

    /**
     * @var \Magento\Email\Model\Template\Config
     */
    private $emailConfig;

    /**
     * Init model
     * Load Website, Group and Store collections
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(\Magento\Email\Model\Template\Config $emailConfig)
    {
        $this->emailConfig = $emailConfig;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return $this->_getDefaultTemplatesAsOptionsArray();
    }

    /**
     * Get default templates as options array
     *
     * @return array
     */
    protected function _getDefaultTemplatesAsOptionsArray()
    {
        $options = array_merge(
            [['value' => '', 'label' => '', 'group' => '']],
            $this->emailConfig->getAvailableTemplates()
        );
        uasort(
            $options,
            function (array $firstElement, array $secondElement) {
                return strcmp($firstElement['label'], $secondElement['label']);
            }
        );

        $forUnset = [
            'catalog_productalert_cron_error_email_template',
            'currency_import_error_email_template',
            'magento_scheduledimportexport_export_failed',
            'system_magento_scheduled_import_export_log_error_email_template',
            'admin_emails_forgot_email_template',
            'magento_scheduledimportexport_import_failed',
            'sitemap_generate_error_email_template',
            'design_email_footer_template',
            'design_email_footer_template/Magento/luma',
            'design_email_header_template',
            'checkout_payment_failed_template',
            'admin_emails_user_notification_template',
            'customer_account_information_change_email_template',
            'sales_email_magento_rma_customer_comment_template'
        ];
        foreach ($options as $key => $template) {
            if (in_array($template['value'], $forUnset)) {
                unset($options[$key]);
            }
        }

        return $options;
    }
}
