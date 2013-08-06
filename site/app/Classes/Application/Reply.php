<?php

namespace Classes\Application;

/**
 * A general class with multiple responses types
 *
 * @package Classes\Application
 */
class Reply extends \Phalcon\Di\Injectable
{

    /**
     * Reply a resource result or a result set
     *
     * @param $values
     * @param null $location
     */
    public function result($values, $location = null)
    {
        $this->_prepare(200, 'Ok', array('data' => $values), $location);
    }

    /**
     * Indicates a resource was created
     *
     * @param $values
     * @param null $location
     */
    public function created($values, $location = null)
    {
        $this->_prepare(201, 'Created', array('data' => $values), $location);
    }

    /**
     * Indicates the resource was updated
     *
     * @param $values
     * @param null $location
     */
    public function updated($values, $location = null)
    {
        $this->_prepare(200, 'Ok', array('data' => $values), $location);
    }

    /**
     * Indicates the resource was deleted
     *
     * @param null $location
     */
    public function deleted($location = null)
    {
        $this->_prepare(410, 'Gone', null, $location);
    }

    /**
     * Reply the user credentials are not valid
     *
     * @param $code
     * @param $property
     * @param $message
     * @param null $details
     * @param null $moreInfo
     */
    public function unauthorized($code, $property, $message, $details = null, $moreInfo = null)
    {
        $this->_prepare(401, 'Unauthorized', array(
            'error' => array(
                'code' => $code,
                'property' => $property,
                'message' => $message,
                'details' => $details,
                'moreInfo' => $moreInfo
            )
        ));
    }

    /**
     * Reply the user is not authorized to access the resource
     *
     * @param $code
     * @param $property
     * @param $message
     * @param null $details
     * @param null $moreInfo
     */
    public function forbidden($code, $property, $message, $details = null, $moreInfo = null)
    {
        $this->_prepare(403, 'Forbidden', array(
            'error' => array(
                'code' => $code,
                'property' => $property,
                'message' => $message,
                'details' => $details,
                'moreInfo' => $moreInfo
            )
        ));
    }

    /**
     * Reply an error is happened in the data input
     *
     * @param $code
     * @param $property
     * @param $message
     * @param null $details
     * @param null $moreInfo
     */
    public function error($code, $property, $message, $details = null, $moreInfo = null)
    {
        $this->_prepare(409, 'Conflict', array(
            'error' => array(
                'code' => $code,
                'property' => $property,
                'message' => $message,
                'details' => $details,
                'moreInfo' => $moreInfo
            )
        ));
    }

    /**
     * Reply about an exception in the system
     *
     * @param $code
     * @param $message
     * @param $file
     * @param $line
     * @param $trace
     */
    public function exception($code, $message, $file, $line, $trace)
    {
        $this->_prepare(409, 'Conflict', array(
            'exception' => array(
                'code' => $code,
                'message' => $message,
                'file' => $file,
                'line' => $line,
                'trace' => $trace
            )
        ));
    }

    /**
     * Reply an internal error in the system
     *
     * @param null $details
     * @param null $moreInfo
     */
    public function internalError($details = null, $moreInfo = null)
    {
        $this->_prepare(500, 'Internal Server Error', array(
            'error' => array(
                'code' => 500,
                'message' => 'Internal Server Error',
                'details' => $details,
                'moreInfo' => $moreInfo,
                'time' => date('U')
            )
        ));
    }

    /**
     * The resource was not found
     *
     * @param null $details
     * @param null $moreInfo
     */
    public function notFound($details = null, $moreInfo = null)
    {
        $this->_prepare(404, 'Not Found', array(
            'error' => array(
                'code' => 404,
                'message' => 'Not Found',
                'details' => $details,
                'moreInfo' => $moreInfo
            )
        ));
    }


    /**
     * Send the prepared reply
     *
     * @param $codeNumber
     * @param $codeMessage
     * @param $response
     * @param null $location
     */
    private function _prepare($codeNumber, $codeMessage, $response, $location = null)
    {
        $location = $this->_buildResourceUrl($location);

        $r = $this->di->get('response');
        $r->setStatusCode($codeNumber, $codeMessage);
        $r->setHeader('Content-Type', 'application/json');
        $r->setJsonContent(
            array(
                'status' => array(
                    'code' => $codeNumber,
                    'message' => $codeMessage
                ),
                'resource' => array(
                    'location' => $location,
                ),
                'response' => $response,
            )
        );
    }

    /**
     * To build the resource address
     *
     * @param $location
     * @return string
     */
    private function _buildResourceUrl($location)
    {
        if (null !== $location && is_array($location)) {
            if (isset($location['route'])) {
                $name = $location['route'];
                $parameters = array();

                if (isset($location['params'])) {
                    $parameters = $location['params'];
                }

                $parameters['for'] = $name;

                try {
                    $uri = '/'.$this->url->get($parameters);
                } catch (\Exception $e) {
                    $uri = '/';
                }

                $request = $this->di->get('request');
                return $request->getScheme().'://'.$request->getHttpHost().$uri;
            }
        }

        $request = $this->di->get('request');
        $uri = $this->di->get('router')->getRewriteUri();
        return $request->getScheme().'://'.$request->getHttpHost().$uri;
    }

}