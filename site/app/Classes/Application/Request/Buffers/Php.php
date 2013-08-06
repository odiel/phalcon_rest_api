<?php

namespace Classes\Application\Request\Buffers;

class Php extends Base
{

    public function read()
    {
        return file_get_contents('php://input');
    }

}