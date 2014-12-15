<?php
/**
 * Conversio
 *
 * @link        https://github.com/leodido/conversio
 * @copyright   Copyright (c) 2014, Leo Di Donato
 * @license     http://opensource.org/licenses/ISC      ISC license
 */
namespace Conversio;

use Traversable;
use Zend\Stdlib\AbstractOptions;
use Zend\Stdlib\ArrayUtils;
use Zend\Filter\AbstractFilter;

/**
 * Class Conversio
 *
 * @author leodido <leodidonato@gmail.com>
 */
class Conversion extends AbstractFilter
{
    /**
     * @var ConversionAlgorithmInterface
     */
    protected $adapter;

    /**
     * @var AbstractOptions|null
     */
    protected $adapterOptions;

    /**
     * Class constructor
     *
     * @param array|string|null|Traversable|ConversionAlgorithmInterface $params Adapter and its options to set (opt.)
     */
    public function __construct($params = null)
    {
        if ($params instanceof Traversable) {
            $params = ArrayUtils::iteratorToArray($params);
        }
        switch(true) {
            case is_string($params):
            case $params instanceof ConversionAlgorithmInterface:
                $this->setAdapter($params);
                break;
            case is_array($params):
                $this->setOptions($params);
                break;
            default:
                break;
        }
    }

    /**
     * Sets conversion adapter
     *
     * @param  string|ConversionAlgorithmInterface $adapter Adapter to use
     * @return $this
     * @throws Exception\InvalidArgumentException
     * @throws Exception\RuntimeException
     */
    public function setAdapter($adapter)
    {
        if (!is_string($adapter) && !$adapter instanceof ConversionAlgorithmInterface) {
            throw new Exception\InvalidArgumentException(sprintf(
                '"%s" expects a string or an instance of ConversionAlgorithmInterface; received "%s"',
                __METHOD__,
                is_object($adapter) ? get_class($adapter) : gettype($adapter)
            ));
        }
        if (is_string($adapter)) {
            if (!class_exists($adapter)) {
                throw new Exception\RuntimeException(sprintf(
                    '"%s" unable to load adapter; class "%s" not found',
                    __METHOD__,
                    $adapter
                ));
            }
            $tmp = new $adapter();
            if (!$tmp instanceof ConversionAlgorithmInterface) {
                throw new Exception\InvalidArgumentException(sprintf(
                    '"%s" expects a string representing an instance of ConversionAlgorithmInterface; received "%s"',
                    __METHOD__,
                    is_object($tmp) ? get_class($tmp) : gettype($tmp)
                ));
            }
            $adapter = $tmp;
        }
        $this->adapter = $adapter;

        return $this;
    }

    /**
     * Returns the current adapter
     *
     * @return ConversionAlgorithmInterface
     * @throws Exception\RuntimeException
     */
    public function getAdapter()
    {
        if (!$this->adapter) {
            throw new Exception\RuntimeException(sprintf(
                '"%s" unable to load adapter; adapter not found',
                __METHOD__
            ));
        }
        return $this->adapter;
    }

    /**
     * Set filter options
     *
     * @param  array|Traversable $options
     * @throws Exception\InvalidArgumentException if options is not an array or Traversable
     * @return $this
     */
    public function setOptions($options)
    {
        if (!is_array($options) && !$options instanceof Traversable) {
            throw new Exception\InvalidArgumentException(sprintf(
                '"%s" expects an array or Traversable; received "%s"',
                __METHOD__,
                is_object($options) ? get_class($options) : gettype($options)
            ));
        }

        foreach ($options as $key => $value) {
            if ($key == 'options') {
                $key = 'adapterOptions';
            }
            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    /**
     * Retrieve adapter name
     *
     * @return string
     */
    public function getAdapterName()
    {
        return $this->getAdapter()->getName();
    }

    /**
     * Set adapter options
     *
     * @param  array|null|Traversable|AbstractOptions $options
     * @return $this
     */
    public function setAdapterOptions($options)
    {
        // Retrieve adapter namespace
        $adapter = $this->getAdapter();
        $adapterClass = get_class($adapter);
        $namespace = substr($adapterClass, 0, strrpos($adapterClass, '\\'));
        // Build full qualified option class name
        $optClass = $namespace . '\\Options\\' . $adapter->getName() . 'Options';
        // Does the option class exist?
        if (!class_exists($optClass)) {
            throw new Exception\DomainException(sprintf(
                '"%s" expects that an options class for the current adapter exists; received "%s"',
                __METHOD__,
                $optClass
            ));
        }
        $opts = new $optClass($options);
        if (!$opts instanceof AbstractOptions) {
            throw new Exception\DomainException(sprintf(
                '"%s" expects the options class to resolve to a valid %s instance; received "%s"',
                __METHOD__,
                'Zend\Stdlib\AbstractOptions',
                $optClass
            ));
        }
        $this->adapterOptions = $opts;

        return $this;
    }

    /**
     * Retrieve adapter options
     *
     * @return AbstractOptions|null
     */
    public function getAdapterOptions()
    {
        return $this->adapterOptions;
    }

    /**
     * Get individual or all options from underlying adapter options object
     *
     * @param  string|null $option
     * @return array|mixed|null
     */
    public function getOptions($option = null)
    {
        $adapterOpts = $this->getAdapterOptions();
        if (!$adapterOpts) {
            return null;
        }
        return is_null($option) ? $adapterOpts->toArray() : $adapterOpts->{$option};
    }

    /**
     * {@inheritdoc}
     */
    public function filter($value)
    {
        if (!is_string($value)) {
            return $value;
        }
        return $this->getAdapter()->convert($value);
    }
}
