<?php

abstract class FTPConnect extends CConnect
{

    /**
     * Массив с конфигурацией
     *
     * @var array
     */
    protected $config;

    /**
     * Подключение к ftp-серверу
     *
     * @var resource
     */
    protected $resource;

    /**
     *
     * @var string
     */
    protected $fileName;


    public function __construct()
    {
        $this->resource = ftp_connect($this->config['host']);

        if ( !$this->resource ) {
            throw new Exception("Невозможно установить соединение с ftp сервером {$this->config['host']}.", 100);
        }

        Debug::Message('Установлено соединение с ' . $this->config['host']);

        $login_result = ftp_login($this->resource, $this->config['user'], $this->config['pass']);

        if ( !$login_result ) {
            throw new Exception("Не удалось произвести вход под именем {$this->config['user']}", 100);
        }

        Debug::Message('Авторизация пройдена успешно.');

        $resChdir = ftp_chdir($this->resource, './' . $this->config['defaultDir']);

        if ($resChdir === FALSE) {
            throw new Exception('Невозможно сменить директорию на FTP-сервере. Сервер: ' . $this->config['host'] . ' Директория: ' . $this->config['defaultDir']);
        }
    }

    public function __destruct()
    {
        ftp_close($this->resource);
    }

    /**
     * Получение списка директорий по маске, в которые нужно копировать файлы с погодой.
     *
     * @return array
     */
    protected function getListTT()
    {
        $listTT = ftp_nlist($this->resource, $this->config['fileMask']);

        sort($listTT, SORT_NATURAL);

        return $listTT;
    }

    /**
     * Скопировать файл с погодой на ftp-сервер.
     *
     * @param string $fileName
     * @return void
     */
    public function putFile($fileName = NULL)
    {
        if ( empty($fileName) || !is_string($fileName) ) {
            throw new Exception('Неверный параметр!');
        }

        $this->fileName = $fileName;

        $localFileName = Config::getInstance()->getFileDir() . $this->fileName;

        if ( !file_exists($localFileName) ) {
            throw new Exception('При копировании файла на сервер прозошла ошибка. Файл ' . $localFileName . ' не существует!');
        }

        foreach ($this->getListTT() as $TT) {
            $remoteFileName = $TT . '/OUT/' . $this->fileName;
            $resultPutFile = @ftp_put($this->resource,  $remoteFileName, $localFileName, FTP_BINARY);
            ($resultPutFile) ? Debug::Message('Файл ' . $this->fileName . ' cкопирован. Путь /'    . $this->config['defaultDir'] . '/' . $remoteFileName)
                   : Debug::Message('Файл ' . $this->fileName . ' НЕ cкопирован. Путь /' . $this->config['defaultDir'] . '/' . $remoteFileName);
        }
    }

}