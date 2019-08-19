<?php

/**
 * УСТАРЕВШИЙ КЛАСС НЕ ИСПОЛЬЗУЕТСЯ!
 * 
 * TODO: реализовать хранение специфических типов источников через массивы или через классы.
 * TODO: полностью переделать архитектуру данного класса!
 *
 * @author Войлоков А.
 */
class FTP
{
    /**
     *
     * @var type
     */
    private static $_instance = null;


    /**
     * Тип подключения к ftp-серверу
     * @var array
     */
    private $connect_type_list;


    /**
     *
     * @var string
     */
    private $connect_type = null;


    /**
     * Параметры подключения к фтп-серверам.
     * TODO: как обеспечить единообразную структуру массива. сделать класс конфига, не?
     *
     * @var array
     */
    private $conn_parameter = array(
        'rs'  => array(
            'host' => '*****',
            'user' => '*****',
            'pass' => '*****',
            'defaultDir' => '*****',
            'fileMask' => 'rs_*',
            'resource' => null,
        ),
        'fsk' => array(
            'host' => '******',
            'user' => '******',
            'pass' => '******',
            'defaultDir' => '******',
            'fileMask' => 'fs_*',
            'resource' => null,
        ),
    );


    /**
     *
     * @throws Exception
     */
    private function __construct()
    {

        foreach ($this->conn_parameter as $type => &$parameters) {
            $parameters['resource'] = @ftp_connect($parameters['host']);

            if ( !$parameters['resource'] ) {
                throw new Exception("Невозможно установить соединения с ftp сервером {$parameters['host']}.", 100);
            }

            $login_result = @ftp_login($parameters['resource'], $parameters['user'], $parameters['pass']);

            if ( !$login_result ) {
                throw new Exception("Не удалось произвести вход под именем {$parameters['user']}", 100);
            }

            @ftp_chdir($parameters['resource'], './' . $parameters['defaultDir']);

            array_push($this->connect_type_list, $type);

        }//foreach
        unset($parameters);
    }


    /**
     *
     * @return type
     */
    public static function getInstance()
    {
        if (self::$_instance === null) { // если экземпляр данного класса  не создан
            self::$_instance = new self;  // создаем экземпляр данного класса
        }
        return self::$_instance; // возвращаем экземпляр данного класса
    }


    /**
     *
     * @return type
     */
    private function __clone()
    {
        return self::$_instance;
    }


    /**
     *
     */
    final public function __destruct()
    {
        self::$_instance = null;
    }


    /**
     * Список директорий в которые будет осуществлено копирование файла
     * с погодой.
     *
     * @return array
     */
    public function listTT()
    {
        //TODO: закешировать или вынести в код так, чтобы вызов был один раз
        $listTT = ftp_nlist($this->_getCurrentConnect(),
                            $this->_getCurrentParameter('fileMask'));

        return $listTT;
    }


    /**
     *
     */
    public function putFile($fileName = NULL, $file_resource = NULL)
    {
        if ( empty($fileName) || !is_resource($file_resource) ) {
            throw new Exception('Неверный параметр.');
        }

        $result = ftp_fput($this->_getCurrentConnect(), $fileName, $file_resource, FTP_ASCII);

        if ( !$result ) {
            throw new Exception('Невозможно скопировать файл на FTP-сервер.');
        }

        return $result;
    }


    /**
     * Установка типа соединения
     * @param string $connectType
     */
    public function setConnectType($connectType = '')
    {
        if ( !in_array($connectType, $this->connect_type_list) ) {
            throw new Exception('Неправильно задан параметр подключения.');
        }

        $this->connect_type = $connectType;
    }


    /**
     *
     */
    private function _getCurrentConnect()
    {
        return $this->_getCurrentParameter('resource');
    }


    /**
     *
     * @param string $parameter
     */
    private function _getCurrentParameter($parameter)
    {
        if ( empty($this->connect_type) ) {
            throw new Exception('Не указан текущий тип подключения к ФТП-серверу.');
        }

        if ( !isset($this->conn_parameter[$this->connect_type][$parameter]) ) {
            throw new Exception("Параметр '{$parameter}' не существует.");
        }

        return $this->conn_parameter[$this->connect_type][$parameter];
    }


}