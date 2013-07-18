<?

namespace Modules\Manage;

class Vendor extends \Classes\Application\Controller
{

    public function get()
    {
        $id = (int) $this->dispatcher->getParam('id');

        $vendor = \Models\Vendor::findFirst(array(
            'conditions' => 'id = ?1',
            'bind' => array(1 => $id)
        ));

        if (false == $vendor) {
            $this->reply->error(5000, 'Vendor not found.', 'Vendor does not exist in our records.');
            return true;
        }

        if (1 == (int) $vendor->deleted) {
            $this->reply->error(5001, 'Vendor not found.', 'Vendor has been removed from our records.');
            return true;
        }

        if ($this->vendor['id'] !== $vendor->id) {
            $this->reply->error(5010, 'Operation not allowed.', 'You are not allow to pull information related to other Vendors rather than yourself.');
            return true;
        }

        $this->reply->result(array(
            'id' => $vendor->id,
            'email' => $vendor->email,
            'firstName' => $vendor->first_name,
            'lastName' => $vendor->last_name,
            'created' => $vendor->created,
            'publicKey' => $vendor->public_key
        ));
    }

    public function post()
    {
        $values = $this->decodeValue('vendor', array('firstName', 'lastName', 'email'));

        if (false === $values) {
            return false;
        }

        $firstName = $this->filter->sanitize(trim($values->firstName), 'string');
        $lastName = $this->filter->sanitize(trim($values->lastName), 'string');
        $email = $this->filter->sanitize(trim($values->email), 'email');

        $vendor = new \Models\Vendor();
        $vendor->email = $email;
        $vendor->first_name = $firstName;
        $vendor->last_name = $lastName;
        $vendor->private_key = \Configuration::generatePrivateKey();
        $vendor->public_key = \Configuration::generatePrivateKey();

        if (false === $vendor->save()) {
            $message = $vendor->getMessages()[0];
            $field = $message->getField();
            $message = $message->getMessage();
            $code = 0;
            if (is_array($message)) {
                $code = $message['code'];
                $message = $message['message'];
            }
            $this->reply->error($code, $field, $message);
            return true;
        }

        $this->reply->created(
            array('id' => $vendor->id),
            array('route' => 'get-vendor', 'params' => array('id' => $vendor->id))
        );

        return true;
    }

    public function put()
    {
        $values = $this->decodeValue('vendor', array('firstName', 'lastName', 'email'));

        $id = (int) $this->dispatcher->getParam('id');
        $firstName = $this->filter->sanitize(trim($values->firstName), 'string');
        $lastName = $this->filter->sanitize(trim($values->lastName), 'string');
        $email = $this->filter->sanitize(trim($values->email), 'email');

        $vendor =  \Models\Vendor::findFirst(array(
            'conditions' => 'id = ?1',
            'bind' => array(1 => $id)
        ));

        if (false == $vendor) {
            $this->reply->error(5000, 'Vendor not found.', 'Vendor does not exist in our records.');
            return true;
        }

        $vendor->email = $email;
        $vendor->first_name = $firstName;
        $vendor->last_name = $lastName;


        if (false === $vendor->save()) {
            $message = $vendor->getMessages()[0];
            $field = $message->getField();
            $message = $message->getMessage();
            $code = 0;
            if (is_array($message)) {
                $code = $message['code'];
                $message = $message['message'];
            }
            $this->reply->error($code, $field, $message);
            return true;
        }

        $this->reply->updated(
            array('id' => 1),
            array('route' => 'get-vendor', 'params' => array('id' => $vendor->id))
        );
        return true;
    }

    public function delete()
    {
        $id = $this->filter->sanitize($this->dispatcher->getParam('id'), 'int');

        $vendor = \Models\Vendor::findFirst(array(
            'conditions' => 'id = ?1',
            'bind' => array(1 => $id)
        ));

        if (false == $vendor) {
            $this->reply->error(5000, 'Vendor not found.', 'Vendor does not exist in our records.');
            return true;
        }

        $vendor->delete();

        $this->reply->deleted(array('route' => 'get-vendors'));
    }

    public function vendors()
    {
        $vendors = \Models\Vendor::find();

        $result = array();

        foreach ($vendors as $vendor) {
            $result[] = array(
                'id' => $vendor->id,
                'email' => $vendor->email,
                'firstName' => $vendor->first_name,
                'lastName' => $vendor->last_name,
            );
        }

        $this->reply->result($result);
    }

}
