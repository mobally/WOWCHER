<?php
/**
 * Cordial Sync
 * © 2017 Cordial Experiences, Inc. All rights reserved.
 */

namespace Cordial\Sync\Model;

class Variable extends \Magento\Email\Model\Template\Filter
{

    /**
     * @var \Cordial\Sync\Model\Email\Template\Filter
     */
    protected $filter;

    protected $includesExist = [];

    /**
     * @var \Cordial\Sync\Model\Api\Email
     */
    protected $api;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param array $variables
     */
    public function __construct(
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Email\Model\Template\Filter $filter,
        \Psr\Log\LoggerInterface $logger,
        \Cordial\Sync\Model\Api\Email $api,
        $variables = []
    ) {
    
        $this->string = $string;
        $this->filter = $filter;
        $this->api = $api;
        $this->logger = $logger;
        $this->setVariables($variables);
    }

    /**
     * Filter the string as template.
     *
     * @param string $value
     * @return string
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function filter($value)
    {
        foreach ([
                     self::CONSTRUCTION_DEPEND_PATTERN => 'dependDirective',
                     self::CONSTRUCTION_IF_PATTERN => 'ifDirective',
                     self::CONSTRUCTION_TEMPLATE_PATTERN => 'templateDirective',
                 ] as $pattern => $directive) {
            if (preg_match_all($pattern, $value, $constructions, PREG_SET_ORDER)) {
                foreach ($constructions as $construction) {
                    $callback = [$this, $directive];
                    if (!is_callable($callback)) {
                        continue;
                    }
                    try {
                        $replacedValue = call_user_func($callback, $construction);
                    } catch (\Exception $e) {
                        throw $e;
                    }
                    $value = str_replace($construction[0], $replacedValue, $value);
                }
            }
        }

        if (preg_match_all(self::CONSTRUCTION_PATTERN, $value, $constructions, PREG_SET_ORDER)) {
            foreach ($constructions as $construction) {
                $callback = [$this, $construction[1] . 'Directive'];
                if (!is_callable($callback)) {
                    continue;
                }
                try {
                    $replacedValue = call_user_func($callback, $construction);
                } catch (\Exception $e) {
                    throw $e;
                }
                $value = str_replace($construction[0], $replacedValue, $value);
            }
        }

        $value = $this->afterFilter($value);
        return $value;
    }


    public function templateDirective($construction)
    {
        $storeId = $this->_storeId;
        $templateParameters = $this->filter->getOriginParameters($construction[2]);
        if (!isset($templateParameters['config_path'])) {
            // Not specified template
            $value = '';
        } else {
            // Including of template
            $key = str_replace('/', '_', $templateParameters['config_path']);
            $key = \Cordial\Sync\Model\Api\Config::API_EL_PREF . $key;
            $key = substr($key, 0, 31);
            if (in_array($key, $this->includesExist)) {
                $exist = true;
            } else {
                $exist = $this->api->getIncludes($key, $storeId);
            }

            if (!$exist) {
                //Need create
                $exist = $this->api->postIncludes($key, $storeId);
            }
            $exist = true;
            if (!$exist) {
                $value = "<!-- {include \"content:$key\"}  -->";
            } else {
                $value = "{include \"content:$key\"}";
                $this->includesExist[] = $key;
            }
        }

        return $value;
    }

    protected function getVariable($value, $default = '{no_value_defined}')
    {
        $res = $this->convertToCordialVariable($value);
        return $res;
    }

    public function convertToCordialVariable($variable, $bracket = true)
    {
        try {
            $variable = preg_replace('/[^\da-z]/i', '_', $variable);
            $variable = trim($variable, '_');
            $variable = preg_replace('/_+/', '_', $variable);
            if ($bracket) {
                $variable = '{$extVars.'.$variable.'}';
            } else {
                $variable = '$extVars.'.$variable;
            }

            return $variable;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return '';
        }
    }

    public function varDirective($construction)
    {
        // just return the escaped value if no template vars exist to process
        if (count($this->templateVars) == 0) {
            $res = $this->convertToCordialVariable($construction[0]);
            return $res;
        }

        list($directive, $modifiers) = $this->explodeModifiers($construction[2], 'escape');
        return $this->applyModifiers($this->getVariable($directive, ''), $modifiers);
    }

    public function layoutDirective($construction)
    {
        $directiveParams = $this->getParameters($construction[0]);
        $res = $this->convertToCordialVariable($construction[0]);
        return $res;
    }

    /**
     * @param string[] $construction
     * @return string
     */
    public function dependDirective($construction)
    {
        if (count($this->templateVars) == 0) {
            $res = '{if '.$this->convertToCordialVariable($construction[1], false).'}'.$construction[2] . '{/if}';
            return $res;
        }

        if ($this->getVariable($construction[1], '') == '') {
            return '';
        } else {
            return $construction[2];
        }
    }

    public function ifDirective($construction)
    {
        if (count($this->templateVars) == 0) {
            $res = '{if '.$this->convertToCordialVariable($construction[1], false).'}'.$construction[2] . '{/if}';
            return $res;
        }

        if ($this->getVariable($construction[1], '') == '') {
            if (isset($construction[3]) && isset($construction[4])) {
                return $construction[4];
            }
            return '';
        } else {
            return $construction[2];
        }
    }

    public function storeDirective($construction)
    {
        $directiveParams = $this->getParameters($construction[0]);
        $res = $this->convertToCordialVariable($construction[0]);
        return $res;
    }

    /**
     * Design params must be set before calling this method
     */
    public function cssDirective($construction)
    {
        $directiveParams = $this->getParameters($construction[0]);
        $res = $this->convertToCordialVariable($construction[0]);
        return $res;
    }

    public function inlinecssDirective($construction)
    {
        $directiveParams = $this->getParameters($construction[0]);
        $res = $this->convertToCordialVariable($construction[0]);
        return $res;
    }

    public function viewDirective($construction)
    {
        $directiveParams = $this->getParameters($construction[0]);
        $res = $this->convertToCordialVariable($construction[0]);
        return $res;
    }

    public function blockDirective($construction)
    {
        $directiveParams = $this->getParameters($construction[0]);
        $res = $this->convertToCordialVariable($construction[0]);
        return $res;
    }
}
