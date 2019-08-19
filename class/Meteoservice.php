<?php

/**
 * Получение погоды с популярного ресурса по прогнозу погоды Яндекс
 *
 * @author Войлоков А.
 */
class Meteoservice extends WeatherService
{
    private $arrCityId = array(
        "Biysk"         => 150,
        "Gorno-Altaysk" => 1066,
        "Barnaul"       => 160,
        "Aleysk"        => 7305,
        "Belokurikha"   => 7603,
        "Zarinsk"       => 7306,
        "Kamen'-na-Obi" => 7586,
        "Kemerovo"      => 176,
        "Novosibirsk"   => 99,
        "Rubtsovsk"     => 157,
        "Slavgorod"     => 7591
    );

    private $xmlstr;

    private $fullFilePath;

    public $connectionString = 'https://xml.meteoservice.ru/export/gismeteo/point/%city%.xml';

    public function __construct(ScriptConfig $scriptProperties)
    {
        parent::__construct($scriptProperties);
        $this->connectionString = str_replace('%city%', $this->arrCityId[$this->cityId], $this->connectionString);

        $this->buildFullFileName();
    }

    private function buildFullFileName()
    {
        list($fileName, $ras) = explode('.', $this->fileName);
        $fileName = $this->className . '_' . $fileName;

        $this->fullFilePath = $this->getDirFilePath() . $fileName . '.json';
    }

    public function run()
    {
        $this->getJSONFile();

        $this->getData();

        $resultUpdate = $this->updateData();

        $resultInsert = $this->insertData();

        Log::add(date('M d H:i:s') . ' ' . $this->cityName . ', затронуто ' . ((int)$resultUpdate + (int)$resultInsert) . " строк.\n");
    }

    private function getJSONFile()
    {
        if (empty($this->fileName)) {
            throw new Exception ('Не указано имя файла.', 200);
        }

        $this->loadFileJsonFromWeb();

        Debug::Message('Загружен файл ' . $this->fullFilePath );
    }

    private  function loadFileJsonFromWeb()
    {
        $executeString = 'wget -q -O ' . $this->fullFilePath  . ' "' . $this->connectionString . '"';

        try {
            @exec($executeString);
        } catch (Exception $e) {
            //вызов этого события крааайне маловероятен
            throw new Exception('Невозможно подключиться к удалённом серверу. ' . $e->getMessage());
        }
    }

    /**
     * @return string
     * @throws Exception
     */
    private function getDirFilePath()
    {
        $dir = Config::getInstance()->getXMLDir();

        if (!is_dir($dir)) {
            Debug::Message('Создание директории ' . $dir);

            $resmk = mkdir($dir);

            if ($resmk === FALSE) {
                throw new Exception('Невозможно создать директорию ' . $dir);
            }
        }

        return $dir;
    }

    private function getData()
    {

        $this->readDataFromJson();

        $xml = new SimpleXMLElement($this->xmlstr);

        foreach ( $xml->REPORT[0]->TOWN->FORECAST as $tempData) {
            //день
            $phenomen = '';
            if ($tempData["hour"] == '09') {
                switch($tempData->PHENOMENA["cloudiness"]){
                    case -1:
                        $phenomen .= 'туман';
                    break;
                    case 0:
                        $phenomen .= 'ясно';
                        break;
                    case 1:
                        $phenomen .= 'малооблачно';
                        break;
                    case 2:
                        $phenomen .= 'облачно';
                        break;
                    case 3:
                        $phenomen .= 'пасмурно';
                        break;
                }

                switch($tempData->PHENOMENA["precipitation"]){
                    case 4:
                        $phenomen .= '; дождь';
                        break;
                    case 5:
                        $phenomen .= '; ливень';
                        break;
                    case 6:
                        $phenomen .= '; снег';
                        break;
                    case 7:
                        $phenomen .= '; снег';
                        break;
                    case 8:
                        $phenomen .= '; гроза';
                        break;
                    case 10:
                        $phenomen .= '; без осадков';
                        break;
                }

                $date = (string)$tempData["year"]. '-' .(string)$tempData["month"]. '-' .(string)$tempData["day"];
                $this->weatherData[$date]['dat'] = $date;
                $this->weatherData[$date]['tday'] = (string)$tempData->TEMPERATURE["max"];
                $this->weatherData[$date]['wind_dir'] = (integer)$tempData->WIND["direction"];
                $this->weatherData[$date]['wind_speed'] = (integer)$tempData->WIND["max"];
                $this->weatherData[$date]['weather_conditions'] = (string)$phenomen;
                $this->weatherData[$date]['pday'] = (string)$tempData->PRESSURE["max"];
            }

            //ночь
            if ($tempData["hour"] == '03') {
                $date = (string)$tempData["year"]. '-' .(string)$tempData["month"]. '-' .(string)$tempData["day"];
                $this->weatherData[$date]['tnight'] = (string)$tempData->TEMPERATURE["max"];
                $this->weatherData[$date]['pnight'] = (string)$tempData->PRESSURE["max"];
            }
        }

    }

    private function readDataFromJson()
    {
        try {
            if (file_exists($this->fullFilePath)) {
                $resultReadFile = file_get_contents($this->fullFilePath);
            } else {
                throw new Exception('Файл "' . $this->fullFilePath . '" не найден. Ошибка wget.');
            }

            if ($resultReadFile === false) {
                throw new Exception('Во время чтения файла "' . $this->fullFilePath . '" возникла ошибка.');
            }

            $this->xmlstr = $resultReadFile;
        } catch (Exception $e) {
            throw new Exception('Невозможно считать данные из файла. ' . $e->getMessage(), 201);
        }
    }


}
