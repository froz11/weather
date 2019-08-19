<?php
/**
 * Created by PhpStorm.
 * User: froz
 * Date: 13.06.19
 * Time: 9:49
 */

class GismeteoService extends WeatherService
{
    protected $token = '5c862a155699f5.76303383';

    private $arrCityId = array(
        "Biysk"         => 4731,
        "Gorno-Altaysk" => 5180,
        "Barnaul"       => 158254,
        "Aleysk"        => 12428,
        "Belokurikha"   => 11427,
        "Zarinsk"       => 12429,
        "Kamen'-na-Obi" => 4718,
        "Kemerovo"      => 4693,
        "Novosibirsk"   => 4690,
        "Rubtsovsk"     => 5176,
        "Slavgorod"     => 13169
    );

    private $data;

    public $connectionString = 'https://api.gismeteo.net/v2/weather/forecast/by_day_part/%city%/?days=5';

    public function __construct(ScriptConfig $scriptProperties)
    {
        parent::__construct($scriptProperties);
        $this->connectionString = str_replace('%city%', $this->arrCityId[$this->cityId], $this->connectionString);

        $this->getDataFromService();
    }

    private function getDataFromService()
    {

        $headers = [
            'Content-Type: application/x-www-form-urlencoded; charset=utf-8',
            'X-Gismeteo-Token:' .$this->token
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $this->connectionString);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = json_decode(curl_exec($ch));
        curl_close($ch);
        foreach ($result->response as $key => $item) {
            if(date(explode(' ', $item->date->local)[1]) < date( '24:00') AND date(explode(' ', $item->date->local)[1]) > date('12:00' )){
                $this->data[explode(' ', $item->date->local)[0]]['dat'] = explode(' ', $item->date->local)[0];
                $this->data[explode(' ', $item->date->local)[0]]['tday'] = round($item->temperature->air->C);
                $this->data[explode(' ', $item->date->local)[0]]['wind_dir'] = $item->wind->direction->degree;
                $this->data[explode(' ', $item->date->local)[0]]['wind_speed'] = $item->wind->speed->m_s;
                $this->data[explode(' ', $item->date->local)[0]]['weather_conditions'] = $this->getPhenomen($item->cloudiness->type, $item->precipitation->type);
                $this->data[explode(' ', $item->date->local)[0]]['pday'] = $item->pressure->mm_hg_atm;}
            else{
                $this->data[explode(' ', $item->date->local)[0]]['tnight'] = round($item->temperature->air->C);
                $this->data[explode(' ', $item->date->local)[0]]['pnight'] = $item->pressure->mm_hg_atm;}
        }
    }

    public function run()
    {
        $this->getData();

        $resultUpdate = $this->updateData();

        $resultInsert = $this->insertData();

        Log::add(date('M d H:i:s') . ' ' . $this->cityName . ', затронуто ' . ((int)$resultUpdate + (int)$resultInsert) . " строк.\n");
    }

    /**
     * @return string
     * @throws Exception
     */

    private function getData()
    {
            foreach ($this->data as $tempData) {
                $this->weatherData[$tempData['dat']]['dat'] = (string)$tempData['dat'];
                $this->weatherData[$tempData['dat']]['tday'] = (string)$tempData["tday"];
                $this->weatherData[$tempData['dat']]['wind_dir'] = (integer)$tempData["wind_dir"];
                $this->weatherData[$tempData['dat']]['wind_speed'] = (integer)$tempData["wind_speed"];
                $this->weatherData[$tempData['dat']]['weather_conditions'] = (string)$tempData['weather_conditions'];
                $this->weatherData[$tempData['dat']]['pday'] = (string)$tempData["pday"];
                $this->weatherData[$tempData['dat']]['tnight'] = (string)$tempData["tnight"];
                $this->weatherData[$tempData['dat']]['pnight'] = (string)$tempData["pnight"];
            }

    }

    private function getPhenomen($cludiness, $precipit)
    {
        $arrClud = [
            "Ясно",
            "Малооблачно",
            "Облачно",
            "Пасмурно",
            101 => "Переменная облачность",
        ];
        $arrPrec = [
            "Нет осадков",
            "Дождь",
            "Снег",
            "Смешанные осадки",
        ];

        return $arrClud[$cludiness] .'; '. $arrPrec[$precipit];
    }

}
