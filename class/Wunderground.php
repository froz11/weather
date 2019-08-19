<?php

/**
 * Получение погоды с популярного ресурса по прогнозу погоды Яндекс
 *
 * @author Войлоков А..
 */
class Wunderground extends WeatherService
{
    private $json;

    private $fullFilePath;

    public $connectionString = 'http://api.wunderground.com/api/a10b7b376ec6afa5/hourly10day/lang:RU/q/Russia/%city%.json';

    public function __construct(ScriptConfig $scriptProperties)
    {
        parent::__construct($scriptProperties);
        $this->connectionString = str_replace('%city%', $this->cityId, $this->connectionString);

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

        foreach ( $this->json->hourly_forecast as $tempData) {
            //день
            if ($tempData->FCTTIME->hour == 12) {
                $date = $tempData->FCTTIME->year . '-' . $tempData->FCTTIME->mon_padded . '-' . $tempData->FCTTIME->mday_padded;
                $this->weatherData[$date]['dat'] = $date;
                $this->weatherData[$date]['tday'] = $tempData->temp->metric;
                $this->weatherData[$date]['wind_dir'] = $tempData->wdir->degrees;
                $this->weatherData[$date]['wind_speed'] = round($tempData->wspd->metric * (10/36), 0); //перевести из км/час в м/с
                $this->weatherData[$date]['weather_conditions'] = $tempData->condition;
                $this->weatherData[$date]['pday'] = $tempData->mslp->metric; //над уровнем моря!!!
            }

            //ночь
            if ($tempData->FCTTIME->hour == 0) {
                $date = $tempData->FCTTIME->year . '-' . $tempData->FCTTIME->mon_padded . '-' . $tempData->FCTTIME->mday_padded;
                $this->weatherData[$date]['tnight'] = $tempData->temp->metric;
                $this->weatherData[$date]['pnight'] = $tempData->mslp->metric; //над уровнем моря!!!
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

            $this->json = json_decode($resultReadFile);
        } catch (Exception $e) {
            throw new Exception('Невозможно считать данные из файла. ' . $e->getMessage(), 201);
        }
    }


}
