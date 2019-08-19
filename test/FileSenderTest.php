<?php

require_once 'PHPUnit/Autoload.php';
require_once dirname(__FILE__).'/../class/CConnect.php';
require_once dirname(__FILE__).'/../class/FTPConnect.php';
require_once dirname(__FILE__).'/../class/RS_Connect.php';
require_once dirname(__FILE__).'/../class/FSK_Connect.php';
require_once dirname(__FILE__).'/../class/ServTerm_Connect.php';
require_once dirname(__FILE__).'/../class/FileSender.php';

class FileSenderTest extends PHPUnit_Framework_TestCase
{
    //проверка корректности подключения к ftp серверу
    public function testConnect()
    {
        try {
            $objectRS  = new FileSender( new RS_Connect() );
            $objectFSK = new FileSender( new FSK_Connect() );
            $objectST  = new FileSender( new ServTerm_Connect() );
        } catch (Exception $e) {
            $this->fail($e->getMessage());
            return;
        }

        $this->assertTrue(TRUE, 'Есть подключение к ftp-серверу.');
    }

    public function testPutFile()
    {


        $this->assertTrue(TRUE);
    }

}