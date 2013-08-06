<?php

namespace Classes\Phalcon;

/**
 * Class Request
 *
 * A wrapper class to read HTTP PUTs like HTTP POSTs
 *
 * @package Classes\Phalcon
 */

class Request extends \Phalcon\Http\Request
{

    /**
     * Get a HTTP PUT value
     *
     * @param $name
     * @param null $filters
     * @param null $defaultValue
     * @return mixed|null
     */
    public function getPut($name, $filters = null, $defaultValue = null)
    {
        $PUT = $this->obtainPutData();

        if (isset($PUT[$name])) {
            $value = $PUT[$name];

            if (is_array($filters)) {
                $filter = new \Phalcon\Filter();

                for ($i = 0 ; $i < count($filters); $i++) {
                    $value = $filter->sanitize($value, $filters[$i]);
                }
            } else if (is_string($filters)) {
                $filter = new \Phalcon\Filter();

                $value = $filter->sanitize($value, $filters);
            }

            return $value;
        } else {
            return $defaultValue;
        }
    }

    /**
     * Has a HTTP PUT value?
     *
     * @param $name
     * @return bool
     */
    public function hasPut($name)
    {
        $PUT = $this->obtainPutData();

        if (isset($PUT[$name])) {
            return true;
        }

        return false;
    }

    public function obtainPutData()
    {
        if (0 === count($this->_putParameters)) {
            parse_str($this->_buffer->read(), $this->_putParameters);
        }

        return $this->_putParameters;
    }

    public function setBufferObject(\Classes\Application\Request\Buffers\Base $buffer)
    {
        $this->_buffer = $buffer;
    }

    public function getBufferObject()
    {
        return $this->_buffer;
    }

    private $_putParameters = array();
    private $_buffer = null;

}