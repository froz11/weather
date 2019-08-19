<?php

class FileSender
{

    private $connect;

    public function __construct(CConnect $object)
    {
        $this->connect = $object;
    }

    public function putFile($fileName = NULL)
    {
        $this->connect->putFile($fileName);
    }

}