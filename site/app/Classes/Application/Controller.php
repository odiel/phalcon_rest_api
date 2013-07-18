<?

namespace Classes\Application;

use Phalcon\Http\Response as HttpResponse,
    Classes\Phalcon\Sanitize\Filters,
    Classes\Application\BaseController;

/**
 * Class Controller
 *
 * A custom controller to provide a security layer for the application,
 * Every controller must inherit from this one in order to keep the application secure.
 *
 * @package Classes\Application
 */
class Controller extends BaseController
{
    const HTTP_METHOD_GET = 'GET';
    const HTTP_METHOD_POST = 'POST';
    const HTTP_METHOD_PUT = 'PUT';
    const HTTP_METHOD_DELETE = 'DELETE';


    /**
     * Checking several things before
     *
     * @return bool
     */
    public function beforeExecuteRoute()
    {
        $this->reply = new Reply();

        //Getting publicKey and time
        $publicKey = $this->filter->sanitize($this->request->get('id'), Filters::ALPHANUM);
        $time = (int) $this->request->get('time');

        //Checking if the time window is between 0 and \Configuration::$application['api']['security']['time']['operations']
        if (abs(time()-$time) > \Configuration::$application['api']['security']['time']['operations']) {
            $this->reply->unauthorized(402, 'time', 'Unable to continue, the request seems to be out of the allowed window time.');
            return false;
        }

        //Checking publicKey length, must be 32 characters long
        if (32 != strlen($publicKey)) {
            $this->reply->unauthorized(403, 'id', 'The id provided has an invalid structure.', 'The length of your id is not 32 characters long.');
            return false;
        }

        //Getting vendor
        $vendor = \Models\Vendor::findFirst(array(
            'conditions' => 'public_key = ?1',
            'bind' => array(1 => $publicKey)
        ));

        //Checking if vendor exists in the system
        if (false === $vendor) {
            $this->reply->unauthorized(404, 'id', 'The id provided is not recognized.', 'The id provided does not exist in the system.');
            return false;
        }

        //Checking if vendor is active
        if (1 === (int) $vendor->deleted) {
            $this->reply->unauthorized(405, 'id', 'We are so sorry but you are not longer active in our system.');
            return false;
        }

        //Checking nonce value
        if (false === $this->checkNonce($this->filter->sanitize($this->request->get('nonce'), Filters::ALPHANUM), $vendor->public_key)) {
            return false;
        }

        //Setting a vendor property to be accessible from any controller
        $this->vendor = array(
            'id' => $vendor->id,
            'publicKey' => $vendor->public_key,
            'privateKey' => $vendor->private_key,
            'email' => $vendor->email,
            'requestTime' => $time,
            'nonce' => $this->request->get('nonce')
        );


        //Getting dispatcher resource
        $dispatcher = $this->dispatcher;
        $resource = $dispatcher->getNamespaceName().'\\'.$dispatcher->getControllerName();
        $action = $dispatcher->getActionName();

        //Checking if the current vendor is allow to access the resource
        if (!$this->acl->isAllowed($vendor->role, $resource, $action)) {
            $this->reply->forbidden(406, 'id', 'You are not allow to the requested resource.');
            return false;
        }
    }

    /**
     * Checking the nonce
     *
     * @param $nonce
     * @param $publicKey
     * @return bool
     */
    protected function checkNonce($nonce, $publicKey)
    {
        //Checking the nonce length
        $l = strlen($nonce);
        if (5 > $l || $l > 40) {
            $this->reply->unauthorized(500, 'nonce', 'The nonce provided has an invalid format.', 'The length of the nonce is not in the range 5~40 characters.');
            return false;
        }

        //Checking if the nonce was issue before
        $nonceMdl = \Models\Nonce::findFirst(array(
            'conditions' => 'id = ?1 AND vendor_public_key = ?2',
            'bind' => array(
                1 => $nonce,
                2 => $publicKey
            )
        ));

        if (false !== $nonceMdl) {
            $this->reply->error(505, 'nonce', 'Unable to proceed, you must request the operation again.');
            return false;
        }

        //Adding the nonce to the DB
        $nonceMdl = new \Models\Nonce();
        $nonceMdl->id = $nonce;
        $nonceMdl->vendor_public_key = $publicKey;
        $nonceMdl->save();

        return true;
    }

    /**
     * Decode a sent value
     *
     * @param $name
     * @param null $expected
     * @param null $method
     * @return bool|mixed
     */
    protected function decodeValue($name, $expected = null, $method = null)
    {
        $value = '';

        if (null == $method) {
            $method = $this->request->getMethod();
        }

        //Checking the HTTP Method to read the value from
        switch ($method) {
            case self::HTTP_METHOD_DELETE:
            case self::HTTP_METHOD_GET:
                $value = $this->request->get($name);
                break;
            case self::HTTP_METHOD_POST:
                $value = $this->request->getPost($name);
                break;
            case self::HTTP_METHOD_PUT:
                $value = $this->request->getPut($name);
                break;
        }

        //Is the value empty?
        if ('' == $value) {
            $this->reply->error(1000, $name, 'Unable to process the data.', '['.$name.'] parameter does not contain any data to process.');
            return false;
        }

        //Decrypting the value
        $value = openssl_decrypt(base64_decode($value), \Configuration::$application['api']['security']['encryption']['method'], md5($this->vendor['privateKey'].$this->vendor['nonce']), null, substr($this->vendor['nonce'], 0, 16));

        //Decryption was good?
        if (false === $value) {
            $this->reply->error(1001, $name, 'Unable to process the data.', 'The decryption of ['.$name.'] parameter has failed, please check the way you are encrypting the data.');
            return false;
        }

        //Every value is expected to be a JSON structure
        $value = json_decode($value);

        if (false === $value) {
            $this->reply->error(1020, $name, 'Data is corrupted.', 'Data was not encoded properly.');
            return false;
        }

        //Every JSON structure must have the same time provided in the request time parameter
        if ((int) $value->requestTime !== $this->vendor['requestTime']) {
            $this->reply->error(1021, $name, 'Data is corrupted.', 'Seems like the [time] parameter has been altered.');
            return false;
        }

        if (is_array($expected)) {
            foreach ($expected as $property) {
                //Checking expected properties in the JSON structure
                if (!property_exists($value, $property)) {
                    $this->reply->error(1022, $name, 'Data is corrupted.', '['.$property.'] property was expected to appear on ['.$name.'] parameter.');
                    return false;
                }
            }
        }

        return $value;
    }

    protected $vendor = null;
    protected $reply = null;
}

