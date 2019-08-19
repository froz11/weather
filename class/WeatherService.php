<?php

abstract class WeatherService
{
        /**
         * Идентификатор населённого пункта в формате,
         * подходящем для конкретного сервиса погоды.
         *
         * @var mixed
         */
        public $cityId;

        public $connectionString;

        public $scriptId;

        public $className;

        public $cityName;

        public $fileName;

        /**
         * Массив данных с данными по населённому пункту
         * готовые к вставке в БД.
         *
         * @var array
         */
        public $weatherData = array();

        public function __construct(ScriptConfig $scriptProperties)
        {
                $this->cityId   = $scriptProperties->CityIdentity;
                $this->cityName = $scriptProperties->CityName;
                $this->scriptId = $scriptProperties->id;
                $this->fileName = $scriptProperties->FileName;
                $this->className = $scriptProperties->ClassName;
        }


        /**
         * Запуск получения и записи погоды в БД.
         * Необходимо обязательно переопределить в потомке!
         */
        public abstract function run();

         /**
         * Обновление данных по датам, прогноз у которых обновился.
         * @return int количество обновлённых строк.
         */
        public function updateData()
        {
                $sql = '';
                $updateRows = 0;
                foreach ($this->weatherData as $date => $val) {
                        $set = array();
                        foreach ($val as $key => $value) {
                                if ($value === (int)$value) {
                                    $set[] = $key . ' = ' . $value;
                                } else {
                                    $set[] = $key . ' = \''.$value.'\'';
                                }
                        }

                        $sql = '
                            select count(*) as count from forecast
                            where Station = \''.$this->cityName.'\' and dat = \''.$date.'\'
                        ';

                        $result = DB::getInstance()->query($sql); //ошибка обрабатывается в классе DB

                        if ($result[0]['count'] > 0) {
                                $sql = '
                                    update forecast
                                    set ' . implode(', ', $set) . '
                                    where Station = \''.$this->cityName.'\' and dat = \''.$date.'\'
                                ';

                                $result = DB::getInstance()->execute($sql);

                                $updateRows = $updateRows + (int)$result;

                                unset($this->weatherData[$date]);
                        }

                }

                return $updateRows;
        }


        /**
         * Вставка данных по числам, по которым отсутствуют данные о прогнозе погоды.
         * @return int количество вставленных строк.
         */
        public function insertData()
        {
                $sql = '';
                $insertRows = 0;

                if (count($this->weatherData) == 0) {
                        return;
                }

                foreach ($this->weatherData as $date => $val) {
                        $field = array();
                        $set   = array();

                        foreach ($val as $key => $value) {
                                $field[] = $key;

                                if ($value === (int)$value) {
                                    $set[] = $value;
                                } else {
                                    $set[] = '\''.$value.'\'';
                                }
                        }

                        $sql = '
                            insert into forecast(Station, '. implode(', ', $field) .')
                            values (\''.$this->cityName.'\', '.  implode(', ', $set) . ')
                        ';

                        $result = DB::getInstance()->execute($sql);
                        echo $result.'<br />';
                        $insertRows = $insertRows + (int)$result;

                }//foreach

                return $insertRows;
        }

}