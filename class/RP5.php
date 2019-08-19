<?php
/**
 * Получение погоды с популярного ресурса по прогнозу погоды RP5
 *
 * @author Войлоков А.
 */
class RP5 extends WeatherService
{

        /**
         * Строка адреса соединения с удалённым сервером,
         * на котором находится файл xml с погодой.
         *
         * @var string
         */
        public $connectionString = 'http://rp5.ru/xml/%city%/00000/ru';

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
        }


        /**
         * Получение данных с удалённого источника и подготовка их
         * к вставке в базу данных.
         *
         * @return void
         */
        private function getData()
        {
                $datetime = ''; $y = ''; $m = ''; $d = '';
                foreach ($this->xml->point->timestep as $step) {
                    list($y, $m, $d) = explode('-', substr($step->datetime, 0, 9));
                    $datetime = $y . '-' . sprintf('%02d', $m) . '-' . $d;

                    if ($step->G == '7') { //ночь
                        $this->weatherData[$datetime]['tnight'] = (int)$step->temperature;
                        $this->weatherData[$datetime]['pnight'] = (int)$step->pressure;
                        $this->weatherData[$datetime]['dat']    = $datetime;
                    }

                    if ($step->G == '19') { //день
                        $this->weatherData[$datetime]['tday'] = (int)$step->temperature;
                        $this->weatherData[$datetime]['pday'] = (int)$step->pressure;
                        $this->weatherData[$datetime]['dat']  = $datetime;
                        $this->weatherData[$datetime]['wind_speed'] = (int)$step->wind_velocity;
                        $this->weatherData[$datetime]['prec_prob']  = (int)$step->humidity;
                    }

                }

        }
}
