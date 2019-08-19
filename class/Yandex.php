<?php
/**
 * Получение погоды с популярного ресурса по прогнозу погоды Яндекс
 *
 * @author Войлоков А..
 */
class Yandex extends WeatherService
{

        /**
         * Строка адреса соединения с удалённым сервером,
         * на котором находится файл xml с погодой.
         *
         * @var string
         */
        public $connectionString = 'http://export.yandex.ru/weather-ng/forecasts/%city%.xml';

        public function __construct(ScriptConfig $scriptProperties)
        {
                parent::__construct($scriptProperties);
                $this->connectionString = str_replace('%city%', $this->cityId, $this->connectionString);
        }

        /**
         *
         */
        public function run()
        {
                //1) Получение файла XML с удалённого сервера
                $this->getXMLFile();

                //2) Интерпритируем полученные данные
                $this->getData();

                //3) выполняется обновление
                $resultUpdate = $this->updateData();

                //4) производится вставка
                $resultInsert = $this->insertData();

                Log::add(date('M d H:i:s') . ' ' . $this->cityName . ', затронуто ' . ((int)$resultUpdate + (int)$resultInsert) . " строк.\n");
        }//function


        /**
         * Получение данных с удалённого источника и подготовка их
         * к вставке в базу данных.
         *
         * @return void
         */
        private function getData()
        {
                foreach ($this->xml->day as $day) {
                        $datetime = (string)$day->attributes();
                        $this->weatherData[$datetime]['dat'] = $datetime;
                        $temp = 'temperature-data';
                        for ($i = 0; $i < count($day->day_part); $i++) {
                                if ($i == 1) { //день
                                    $this->weatherData[$datetime]['tday'] = (int)$day->day_part[$i]->$temp->avg[0];
                                    $this->weatherData[$datetime]['pday'] = (int)$day->day_part[$i]->pressure[0];
                                    $this->weatherData[$datetime]['prec_prob'] = (int)$day->day_part[$i]->pressure[0];
                                }

                                if ($i == 3) { //ночь
                                    $this->weatherData[$datetime]['tnight'] = (int)$day->day_part[$i]->$temp->avg[0];
                                    $this->weatherData[$datetime]['pnight'] = (int)$day->day_part[$i]->pressure[0];
                                }
                        }
                }

        }//function

}