<?

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
        if (0 === count($this->_putParameters) && false === $this->_parsed) {
            $this->parsePutParameters();
            $this->_parsed = true;
        }

        if (isset($this->_putParameters[$name])) {
            $value = $this->_putParameters[$name];

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
        if (0 === count($this->_putParameters)) {
            $this->parsePutParameters();
        }

        if (isset($this->_putParameters[$name])) {
            return true;
        }

        return false;
    }

    /**
     * Parse the HTTP PUT values
     */
    protected function parsePutParameters()
    {
        parse_str(file_get_contents('php://input'), $this->_putParameters);
    }

    private $_putParameters = array();
    private $_parsed = false;

}