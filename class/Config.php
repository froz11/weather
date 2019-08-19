<?php

class Config
{

    private static $_instance;

    /**
     *
     * @var string
     */
    private $FileDir;

    private function __construct()
    {
            $this->FileDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR;
    }

    
    public static function getInstance()
    {
            if ( empty(self::$_instance) ) {
                self::$_instance = new self;
            }
            return self::$_instance;
    }


    public function getFileDir()
    {
            return $this->FileDir;
    }

}

