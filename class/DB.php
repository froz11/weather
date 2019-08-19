<?php

class DB
{
    private $host;
    private $user;
    private $pass;
    private $db;

    private $connect;
    protected static $_instance;

    private function __construct()
    {
        $db_conf_file = dirname(__DIR__) . '/config/db_config.php';
        if (!file_exists($db_conf_file)) {
            throw new Exception('Не найден файл конфигурации. "' . $db_conf_file . '"');
        }

        $db_conf = include_once($db_conf_file);
        $this->host = $db_conf['host'];
        $this->db = $db_conf['db'];
        $this->user = $db_conf['user'];
        $this->pass = $db_conf['pass'];

        $this->connect = new PDO("mysql:host=$this->host;dbname=$this->db;charset=utf8", $this->user, $this->pass);

        if (!$this->connect) {
            throw new SQLException('Невозможно установить соединения с БД.', 100);
        }
    }

    private function __clone()
    {
    }

    final public function __destruct()
    {
        self::$_instance = null;
    }

    public static function getInstance() // получить экземпляр данного класса
    {
        if ( empty(self::$_instance) ) { // если экземпляр данного класса  не создан
            self::$_instance = new self;  // создаем экземпляр данного класса
        }
        return self::$_instance; // возвращаем экземпляр данного класса
    }


    /**
     * Выполнение запросов вида insert, update.
     *
     * @param string $sql
     * @return int количество затронутых строк
     */
    public function execute($sql)
    {
        $object = self::$_instance;

        $sth = $object->connect->prepare($sql);
        if ($sth->execute() === FALSE) {
            throw new Exception('Невозможно выполнить запрос: "'.$sql.'"', 101);
        }

        return $sth->rowCount();
    }

    /**
     * Выполнение запросов на выборку.
     *
     * @param string $sql
     * @return array результат запроса
     */
    public function query($sql)
    {
        $object = self::$_instance;

        $sth = $object->connect->prepare($sql);

        if ($sth === FALSE) {
            throw new Exception('Невозможно выполнить запрос: "'.$sql.'"', 102);
        }

        $sth->execute();

        return $sth->fetchAll();
    }
}
