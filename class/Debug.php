<?php

/**
 * Общий класс конфигурации всего приложения.
 * Настройки необходимые в разных местах программы.
 */
class Debug
{

    /**
     * Режим отладки.
     * @var bool TRUE включает режим отладки, FALSE выключает.
     */
    private static $IsDebugMode = FALSE;

    /**
     * Возвращает текущий режим отладки.
     * @return bool
     */
    public static function IsDebug()
    {
        return self::$IsDebugMode;
    }

    /**
     * @param bool $DebugMode
     * @throws Exception
     */
    public static function SetDebug($DebugMode)
    {
        if (!is_bool($DebugMode)) {
            throw new Exception('Передан параметр неподдерживаего типа. Ожидалось "bool" пришло "' . gettype($DebugMode) . '".' );
        }
        self::$IsDebugMode = $DebugMode;
    }

    /**
     * Вывод отладочного сообщения во время пробных запусков.
     *
     * @param string $Message
     */
    public static function Message($Message = NULL)
    {
        if ( !is_string($Message) ) {
            throw new Exception('Передан параметр неподдерживаего типа. Ожидалось "string" пришло "' . gettype($Message) . '".' );
        }

        if ( self::$IsDebugMode ) {
            echo $Message, "\n";
        }
    }
}
