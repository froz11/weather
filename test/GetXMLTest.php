<?php

require_once 'PHPUnit/Autoload.php';
require_once dirname(__FILE__).'/../class/ConfigureRecord.php';
require_once dirname(__FILE__).'/../class/ScriptConfig.php';
require_once dirname(__FILE__).'/../class/DB.php';
require_once dirname(__FILE__).'/../class/WeatherService.php';
require_once dirname(__FILE__).'/../class/Yandex.php';
require_once dirname(__FILE__).'/../class/Log.php';

class GetXMLTest extends PHPUnit_Framework_TestCase
{
    //тестируем скачивание xml файла
    public function testGetXML()
    {

        $object = ConfigureRecord::buildRecord(2);

        $yandex = new Yandex($object);
        $yandex->getXMLFile();

        $this->assertTrue(TRUE);
    }

    public function testRun()
    {

        $object = ConfigureRecord::buildRecord(2);

        $yandex = new Yandex($object);
        $yandex->run();

        $this->assertTrue(TRUE);
    }


}
