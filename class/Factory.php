<?php

/**
 * Статичный класс для порождение объектов типа "запись таблицы".
 */
class Factory
{

    /**
     * Идентификатор записи в БД.
     * @var integer
     */
    private static $recordId;


    /**
     * Название таблицы, которая содержит конфигурационные записи.
     * @var string
     */
    private static $table = 'scripts';


    /**
     * Порождение объектов
     * @param integer $id
     * @return ScriptConfig
     */
    public static function createRecord($id = null)
    {
        if ( empty($id) || !is_int($id) ) {
            throw new Exception('Передан параметр неверного типа.');
        }

        self::$recordId = $id;
        $recordData = self::getRecordData();

        $object = new ScriptConfig();

        $object->setRecordId((int)$id);

        foreach ($recordData as $key => $value) {
            $object->$key = $value;
        }

        return $object;
    }


    /**
     * Получение данных из таблицы.
     * @return array
     */
    private static function getRecordData()
    {
        $sql = 'select * from ' . self::$table . ' where id = ' . self::$recordId . ' limit 1';
        $recordArray = DB::getInstance()->query($sql);

        if ( $recordArray === FALSE ) {
            throw new Exception('Невозможно выполнить запрос: "' . $sql . '"');
        }

        return $recordArray[0];
    }

}