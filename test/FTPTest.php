<?php

require_once 'PHPUnit/Autoload.php';
require_once dirname(__FILE__).'/../class/FTP.php';

class FTPTest extends PHPUnit_Framework_TestCase
{
    //проверка корректности подключения к ftp серверу
    public function testConnect()
    {
        try {
            $my = FTP::getInstance();
        } catch (Exception $e) {
            $this->fail('ОШИБКА! Невозможно подключиться к ftp-серверу.');
            return;
        }

        $this->assertTrue(TRUE, 'Есть подключение к ftp-серверу.');
    }

    //получение списка торговых точек.
    public function testListTT()
    {
        $my = FTP::getInstance();

        $my->setConnectType('rs');
        $listTT = $my->ListTT();

        if (count($listTT) === 0) {
            $this->fail('ОШИБКА! Мало торговых точек.');
        }

        foreach ($listTT as $value) {
            if (substr($value, 0, 3) !== 'rs_') {
                $this->fail('Неправильный магазин! ' . $value);
            }
        }

        $my->setConnectType('fsk');
        $listTT = $my->ListTT();

        if (count($listTT) === 0) {
            $this->fail('ОШИБКА! Мало торговых точек.');
        }

        foreach ($listTT as $value) {
            if (substr($value, 0, 3) !== 'fs_') {
                $this->fail('Неправильный магазин! ' . $value);
            }
        }

        $this->assertTrue(TRUE, 'Список ТТ корректен.');
    }


}