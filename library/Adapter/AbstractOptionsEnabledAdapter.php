<?php
/**
 * Conversio
 *
 * @link        https://github.com/leodido/conversio
 * @copyright   Copyright (c) 2014, Leo Di Donato
 * @license     http://opensource.org/licenses/ISC      ISC license
 */
namespace Conversio\Adapter;

use Conversio\Exception;
use Conversio\ConversionAlgorithmInterface;
use Zend\Stdlib\AbstractOptions;

/**
 * Class AbstractOptionsEnabledAdapter
 */
abstract class AbstractOptionsEnabledAdapter implements ConversionAlgorithmInterface
{
    /**
     * @var AbstractOptions
     */
    protected $options = null;

    /**
     * Set the adapter options instance
     *
     * @param AbstractOptions $options
     * @return $this
     */
    public function setOptions(AbstractOptions $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Retrieve the adapter options instance
     *
     * @return AbstractOptions
     */
    public function getOptions()
    {
        if (!$this->options) {
            throw new Exception\RuntimeException(sprintf(
                'No options instance set for the adapter "%s"',
                get_class($this)
            ));
        }

        return $this->options;
    }
}
