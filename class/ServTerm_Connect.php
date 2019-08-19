<?php

class ServTerm_Connect extends CConnect
{

    public function putFile($fileName = NULL)
    {
        exec("cd " . Config::getInstance()->getFileDir() . " && /usr/bin/smbclient //SERV-TERM/Prognoz 111 -c 'put {$fileName}' -I serv-term -U weather -W TDANIX > /dev/null 2>&1");
        Debug::Message('Файл ' . $fileName . ' cкопирован. Путь //SERV-TERM/Prognoz/' . $fileName);
    }

}