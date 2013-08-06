<?php

namespace Models;

use \Phalcon\Mvc\Model\Validator\StringLength,
    \Phalcon\Mvc\Model\Validator\PresenceOf,

    \Phalcon\Mvc\Model\Behavior\Timestampable;

class Nonce extends \Phalcon\Mvc\Model
{

    public $id;
    public $vendor_public_key;
    public $created;

    public function getSource()
    {
        return 'nonce';
    }

    public function initialize()
    {
        $this->addBehavior(new Timestampable(
            array(
                'beforeCreate' => array(
                    'field' => 'created',
                    'format' => 'Y-m-d H:i:s'
                )
            )
        ));

        $this->belongsTo('vendor_public_key', '\Models\Vendor', 'public_key');
    }

    public function validation()
    {
        $this->validate(new PresenceOf(array(
            'field' => 'id',
            'message' => array('code' => 520, 'message' => 'Id is a mandatory field.')
        )));

        $this->validate(new PresenceOf(array(
            'field' => 'vendor_public_key',
            'message' => array('code' => 525, 'message' => 'Vendor Public Key is a mandatory field.')
        )));

        $this->validate(new StringLength(array(
            'field' => 'id',
            'min' => 5,
            'max' => 40,
            'messageMinimum' => array('code' => 530, 'message' => 'Nonce Id has to be 5 characters long minimum.'),
            'messageMaximum' => array('code' => 531, 'message' => 'Nonce Id has to be 40 characters long maximum.'),
        )));

        $this->validate(new StringLength(array(
            'field' => 'vendor_public_key',
            'min' => 32,
            'max' => 32,
            'messageMinimum' => array('code' => 540, 'message' => 'Vendor Id has to be 32 characters long.'),
            'messageMaximum' => array('code' => 540, 'message' => 'Vendor Id has to be 32 characters long.')
        )));

        if (true === $this->validationHasFailed()) {
            return false;
        }
    }

}