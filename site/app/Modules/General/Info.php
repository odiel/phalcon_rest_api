<?php

namespace Modules\General;

class Info extends \Phalcon\Mvc\Controller
{

    public function get()
    {
        $reply = new \Classes\Application\Reply();
        $reply->result(array('message' => 'Hi!', 'time'=>time()));
    }

}