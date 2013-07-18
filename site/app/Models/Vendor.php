<?

namespace Models;

use \Phalcon\Mvc\Model\Validator\Uniqueness,
    \Phalcon\Mvc\Model\Validator\StringLength,
    \Phalcon\Mvc\Model\Validator\PresenceOf,
    \Phalcon\Mvc\Model\Validator\Email,

    \Phalcon\Mvc\Model\Behavior\SoftDelete,
    \Phalcon\Mvc\Model\Behavior\Timestampable;

class Vendor extends \Phalcon\Mvc\Model
{

    public $id;
    public $public_key;
    public $private_key;
    public $first_name;
    public $last_name;
    public $email;
    public $role;
    public $created;
    public $deleted;

    public function getSource()
    {
        return 'vendor';
    }

    public function initialize()
    {
        $this->addBehavior(new SoftDelete(
            array(
                'field' => 'deleted',
                'value' => 1,
            )
        ));

        $this->addBehavior(new Timestampable(
            array(
                'beforeCreate' => array(
                    'field' => 'created',
                    'format' => 'Y-m-d H:i:s'
                )
            )
        ));

        $this->hasMany('public_key', '\Models\Nonce', 'vendor_public_key');
    }

    public function validation()
    {
        if (null == $this->deleted) {
            $this->deleted = 0;
        }

        $this->validate(new PresenceOf(array(
            'field' => 'email',
            'message' => array('code' => 5100, 'message' => 'Email is a mandatory field.')
        )));

        $this->validate(new StringLength(array(
            'field' => 'email',
            'min' => 5,
            'max' => 100,
            'messageMinimum' => array('code' => 5101, 'message' => 'Email has to be minimum 5 characters long.'),
            'messageMaximum' => array('code' => 5102, 'message' => 'Email has to be maximum 100 characters long.'),
        )));

        $this->validate(new Email(array(
            'field' => 'email',
            'message' => array('code' => 5103, 'message' => 'Email has not a valid structure.'),
            'code' => 102
        )));

        $this->validate(new PresenceOf(array(
            'field' => 'first_name',
            'message' => array('code' => 5120, 'message' => 'First Name is a mandatory field.')
        )));

        $this->validate(new StringLength(array(
            'field' => 'first_name',
            'min' => 3,
            'max' => 50,
            'messageMaximum' => array('code' => 5121, 'message' => 'First Name has to be minimum 5 characters long.'),
            'messageMinimum' => array('code' => 5122, 'message' => 'First Name has to be maximum 50 characters long.'),
        )));

        $this->validate(new PresenceOf(array(
            'field' => 'last_name',
            'message' => array('code' => 5140, 'message' => 'Last Name is a mandatory field.')
        )));

        $this->validate(new StringLength(array(
            'field' => 'last_name',
            'min' => 3,
            'max' => 50,
            'messageMaximum' => array('code' => 5141, 'message' => 'Last Name has to be minimum 5 characters long.'),
            'messageMinimum' => array('code' => 5142, 'message' => 'Last Name has to be maximum 100 characters long.')
        )));

        $this->validate(new PresenceOf(array(
            'field' => 'public_key',
            'message' => array('code' => 5160, 'message' => 'Public Key is a mandatory field.')
        )));

        $this->validate(new PresenceOf(array(
            'field' => 'private_key',
            'message' => array('code' => 5180, 'message' => 'Private Key is a mandatory field.')
        )));

        if (0 == strlen($this->role)) {
            $this->role = 'Vendor';
        } else {
            $this->validate(new PresenceOf(array(
                'field' => 'vendor',
                'message' => array('code' => 5160, 'message' => 'Public Key is a mandatory field.')
            )));
        }

        if (true === $this->validationHasFailed()) {
            return false;
        }

        $this->validate(new Uniqueness(array(
            'field' => 'email',
            'message' => array('code' => 5200, 'message' => 'A vendor is already registered with the same email address.')
        )));

        if (true === $this->validationHasFailed()) {
            return false;
        }

        $this->validate(new Uniqueness(array(
            'field' => 'public_key',
            'message' => array('code' => 5300, 'message' => 'A vendor has already the same public key.')
        )));

        if (true === $this->validationHasFailed()) {
            return false;
        }

        $this->validate(new Uniqueness(array(
            'field' => 'private_key',
            'message' => array('code' => 5400, 'message' => 'A vendor has already the same private key.')
        )));

        if (true === $this->validationHasFailed()) {
            return false;
        }
    }

}