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

namespace Cordial\Sync\Model\Email\Template;

class Filter extends \Magento\Email\Model\Template\Filter
{

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    public function layoutDirective($construction)
    {
        return $this->setCordialVars($construction);
    }

    public function varDirective($construction)
    {
        return $this->setCordialVars($construction);
    }

    protected function setCordialVars($construction)
    {

        $functionName = debug_backtrace()[1]['function'];
        $value = parent::$functionName($construction);
        $this->setCordialVariable($construction[0], $value);
        return $value;
    }

    public function getOriginParameters($value)
    {
        return parent::getParameters($value);
    }

    protected function getVariable($value, $default = '{no_value_defined}')
    {
        $res = parent::getVariable($value, $default);
        $this->setCordialVariable($value, $res);
        return $res;
    }

    protected function setCordialVariable($var, $value)
    {
        try {
            try {
                //
                $value = is_object($value) ? '' : (string)$value;
            } catch (\Exception $e) {
                $this->_logger->info($var . ': ' . $e->getMessage());
            }

            if (!$this->registry) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $this->registry = $objectManager->get(\Magento\Framework\Registry::class);
            }

            $cordialVars = ['store_id' => $this->getStoreId()];
            if ($this->registry->registry(\Cordial\Sync\Helper\Config::CORDIAL_VARS)) {
                $cordialVars = $this->registry->registry(\Cordial\Sync\Helper\Config::CORDIAL_VARS);
                $this->registry->unregister(\Cordial\Sync\Helper\Config::CORDIAL_VARS);
            }

            $var = $this->convertToCordialVariable($var);
            if (empty($var)) {
                return true;
            }

            $cordialVars[$var] = $value;
            $this->registry->register(\Cordial\Sync\Helper\Config::CORDIAL_VARS, $cordialVars);
        } catch (\Exception $e) {
            $this->_logger->info($e->getMessage());
        }
    }

    public function convertToCordialVariable($variable)
    {
        try {
            $variable = preg_replace('/[^\da-z]/i', '_', $variable);
            $variable = trim($variable, '_');
            $variable = preg_replace('/_+/', '_', $variable);
            return $variable;
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
            return '';
        }
    }

    public function viewDirective($construction)
    {
        return $this->setCordialVars($construction);
    }

    public function mediaDirective($construction)
    {
        return $this->setCordialVars($construction);
    }

    public function storeDirective($construction)
    {
        return $this->setCordialVars($construction);
    }

    public function transDirective($construction)
    {
        return $this->setCordialVars($construction);
    }

    protected function getTransParameters($value)
    {
        if (preg_match(self::TRANS_DIRECTIVE_REGEX, $value, $matches) !== 1) {
            return ['', []];  // malformed directive body; return without breaking list
        }
        $text = stripslashes($matches[2]);
        $params = [];
        if (!empty($matches[3])) {
            $params = $this->getParameters($matches[3]);
        }
        return [$text, $params];
    }

    /**
     * Return associative array of parameters.
     *
     * @param string $value raw parameters
     * @return array
     */
    protected function getParameters($value)
    {
        $tokenizer = new \Magento\Framework\Filter\Template\Tokenizer\Parameter();
        $tokenizer->setString($value);
        $params = $tokenizer->tokenize();
        foreach ($params as $key => $value) {
            if (substr($value, 0, 1) === '$') {
                $params[$key] = $this->getVariable(substr($value, 1), null);
            }
        }
        return $params;
    }
}
