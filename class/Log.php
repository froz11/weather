<?php

/**
 * Логирование работы скрипта.
 *
 * @author Войлоков А..
 */
class Log
{
        /**
         * @var string полное имя файла куда будет писаться лог.
         */
        public static $fileLog   = '/var/log/weather.log';
        public static $fileError = '/var/log/weather.err';

        public static $errorList = array(
            //ошибки БД
            100 => 'DB connect fail.',
            101 => 'Can\'t execute sql.',
            102 => 'Can\'t query sql',

            //ошибки файла
            200 => 'Script filename error.',
            201 => 'Can\'t read file.',
            202 => 'File is not xml.',
            203 => 'Can\'t delete file.',
            204 => 'Can\'t create file.',
            205 => 'No data for file.',

            //ошибки smb
            300 => '',
        );

        /**
         * Логирование работы приложения.
         * Записываем каждый чих скрипта.
         *
         * @param string $string
         * @param integer $code
         * @return void
         */
        public static function add($string, $code = NULL)
        {
                $path = self::$fileLog;
                if (!file_exists($path)) {
                    $file = @fopen($path, 'w');
                    if (!is_bool($file)) {
                        @fclose($file);
                    }
                }

                $file = @file_put_contents($path, $string, FILE_APPEND);

                if ($code !== NULL) {
                    //добавляем ошибку в файл weather.err
                    $path = self::$fileError;
                    if (!file_exists($path)) {
                        $file = @fopen($path, 'w');
                        if (!is_bool($file)) {
                            @fclose($file);
                        }
                    }
                    $errorByCode = (!empty(self::$errorList[$code])) ? self::$errorList[$code] : 'String is NULL, code is ' . (int)$code . '.';
                    $file = @file_put_contents($path, date('M d H:i:s') . ' ' . $errorByCode . "\n", FILE_APPEND);
                }

                Debug::Message($string);
                self::addToDB($string, $code);
        }//function


        /**
         * Удаляем файл с ошибкой.
         * В файле ошибок содержаться критические сообщения,
         * которые должны быть отправлены по смс.
         */
        public static function deleteErrorFile()
        {
                if (file_exists(self::$fileError)) {
                    $resDelete = @unlink(self::$fileError);
                    if ($resDelete === FALSE) {
                        //каламбурчик небольшой, не можем удалить файл.
                        self::add(date('M d H:i:s') . ' Невозможно удалить файл ' . self::$fileError . '!');
                    }
                }
        }//function

        /**
         * Добавление лога в базу данных.
         */
        private static function addToDB($string = NULL, $code = NULL)
        {
            $sql = 'INSERT INTO log(Message, ErrorCode) VALUES ("'.addslashes($string).'", '.(int)$code.')';
            DB::getInstance()->execute($sql);
        }

}