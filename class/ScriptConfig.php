<?php

/**
 *
 * @author Войлоков А. <1770183@gmail.com>
 */
class ScriptConfig
{

    /**
     *
     * @var integer
     */
    private $record_id;

    /**
     *
     * @return void
     */
    public function updateTimeRun()
    {

        if ( empty($this->record_id) ) {
            throw new Exception('Не определён идентификатор записи скрипта. ' . __FILE__ . ':' . __METHOD__ . ':' . __LINE__ );
        }

        $query = 'UPDATE scripts
                  SET LastRun = NOW(),
                  NextRun = date_ADD(NOW(), INTERVAL GREATEST( ROUND(1440/GREATEST(RunPerDay,1)), 1 ) MINUTE)
                  WHERE id = ' . $this->record_id;

        DB::getInstance()->execute($query);

    }

    /**
     *
     * @param integer $id
     * @throws Exception
     */
    public function setRecordId($id = NULL)
    {
        if ( !is_int($id) ) {
            throw new Exception('Недопустимый тип параметра.' . __FILE__ . ':' . __METHOD__ . ':' . __LINE__);
        }
        $this->record_id = $id;
    }

    /**
     *
     * @return integer
     */
    public function getRecordId()
    {
        return $this->record_id;
    }


}