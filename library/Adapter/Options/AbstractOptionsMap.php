<?php
/**
 * Conversio
 *
 * @link        https://github.com/leodido/conversio
 * @copyright   Copyright (c) 2014, Leo Di Donato
 * @license     http://opensource.org/licenses/ISC      ISC license
 */
namespace Conversio\Adapter\Options;

use Conversio\Exception;
use Zend\Stdlib\AbstractOptions;
use Zend\Stdlib\ArrayUtils;

/**
 * Class AbstractOptionsMap
 */
class AbstractOptionsMap extends AbstractOptions
{
    /**
     * Hash map containing options configuration: maps the option name to its allowed values
     *
     * @var array
     */
    protected $config = [];

    /**
     * @var array
     */
    protected $options = [];

    /**
     * Ctor
     *
     * @param  array|\Traversable|null $options
     * @throws Exception\DomainException
     */
    public function __construct($options = null)
    {
        if (!ArrayUtils::isHashTable($this->config, false)) {
            throw new Exception\DomainException(sprintf(
                '"%s" expects that options map "%s" is an hash table',
                __METHOD__,
                '$map'
            ));
        }
        parent::__construct($options);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return $this->options;
    }

    /**
     * Abstract option setter with validation
     * If option can have the specified value then it is set, otherwise this method throws exception
     *
     * Tip: call it into your setters.
     *
     * @param $key
     * @param $value
     * @return $this
     * @throws Exception\DomainException
     * @throws Exception\InvalidArgumentException
     */
    protected function setOption($key, $value)
    {
        if (!isset($this->config[$key])) {
            throw new Exception\DomainException(
                sprintf(
                    'Option "%s" does not exist; available options are (%s)',
                    $key,
                    implode(
                        ', ',
                        array_map(
                            function ($opt) {
                                return '"' . $opt . '"';
                            },
                            array_keys($this->config)
                        )
                    )
                )
            );
        }
        if (!ArrayUtils::isList($this->config[$key], false)) {
            throw new Exception\DomainException(sprintf(
                'Option "%s" is not a valid list of allowed values',
                $key
            ));
        }
        if (!ArrayUtils::inArray($value, $this->config[$key], true)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Option "%s" can not be set to value "%s"; allowed values are (%s)',
                $key,
                $value,
                implode(
                    ', ',
                    array_map(
                        function ($val) {
                            return '"' . $val . '"';
                        },
                        $this->config[$key]
                    )
                )
            ));
        }

        $this->options[$key] = $value;
        return $this;
    }
}
