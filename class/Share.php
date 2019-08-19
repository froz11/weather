<?php
/**
 * Копирование файлов с погодой на общедоступные шары.
 */
class Share
{
    public static $fileName = '';
    public static $cityName = '';
    public static $file_resource;

    /**
     *
     * @param type $script
     * @throws Exception
     */
    public static function sendFileToShare(ScriptConfig $script)
    {
        if ( $script->IsShare == 1) {
            self::$fileName = $script->FileName;
            self::$cityName = $script->CityName;

            self::run();
        }
    }

    /**
     * Запуск метода копирования файлов на шары.
     */
    public static function run()
    {
        self::_putDataToFile();


        $fileSenderRS = new FileSender( new RS_Connect() );
        $fileSenderRS->putFile(self::$fileName);

        $fileSenderFSK = new FileSender( new FSK_Connect() );
        $fileSenderFSK->putFile(self::$fileName);

        $fileSenderST = new FileSender( new ServTerm_Connect() );
        $fileSenderST->putFile(self::$fileName);
    }

    /**
     * Создание файла с данными о погоде.
     *
     * @throws Exception
     */
    private static function _putDataToFile()
    {
        $full_filename = self::_getFileDir() . self::$fileName;
        self::$file_resource = @fopen($full_filename, 'w+');
        if (self::$file_resource === FALSE) {
            throw new Exception('Невозможно создать файл ' . $full_filename, 204);
        }

        $temperatureData = self::_getTempData();

        foreach ($temperatureData as $value) {
            fputs(self::$file_resource, "" . $value['fcdate'] . "~" . $value['tday'] . "~\r\n");
        }

        fclose(self::$file_resource);
        Debug::Message('Файл ' . $full_filename . ' создан.');
    }

    /**
     * Возвращает директорию, содержащую файлы с погодой,
     * в случае отсутствия директории создаёт её.
     *
     * @return string
     * @throws Exception
     */
    private static function _getFileDir()
    {
        $full_filename = Config::getInstance()->getFileDir();

        if ( !is_dir($full_filename) ) {
            Debug::Message('Создание директории ' . $full_filename);

            $resmk = mkdir($full_filename);

            if ($resmk === FALSE) {
                throw new Exception('Невозможно создать директорию ' . $full_filename);
            }
        }

        return $full_filename;
    }

    /**
     *  Получение погодных данных по городу
     *
     * @return array
     * @throws Exception
     */
    private static function _getTempData()
    {
        //Чурина Диана попросила ограничить выгрузку значений температуры двумя неделями
        //2014-07-18 Чурина Диана попросила выгружать три недели: две назад и одну вперёд
        $sql = "SELECT
                    date_format(dat, '%d.%m.%y') as fcdate,
                    tday
                FROM forecast
                WHERE 1 = 1
                    AND Station = '".addslashes(self::$cityName)."'
                    AND tday IS NOT NULL
                    AND dat BETWEEN date_add(date_format(now(), GET_FORMAT(DATE, 'JIS')), INTERVAL -14 DAY)
                                AND date_add(date_format(now(), GET_FORMAT(DATE, 'JIS')), INTERVAL 7 DAY)
                ORDER BY dat asc";

        $result = DB::getInstance()->query($sql);

        if (count($result) == 0) {
            throw new Exception('Нет данных для создания файла.', 205);
        }

        return $result;
    }

}