<?php
namespace app\components;
use yii\base\Component;
use yii\base\Exception;
use yii\base\Object;

class FullName extends Object
{
    /**
     * @var string Фамилия
     */
    public $surname = '';

    /**
     * @var string Имя
     */
    public $name = '';

    /**
     * @var string Отчество
     */
    public $patronymic = '';
}

class StrNum extends Object
{
    public $value;
    public $unit;
}

class Morpher extends Component
{
    /**
     * @const Базовая форма, именительный падеж
     */
    const CASE_BASE = 0;

    /**
     * @const Родительный падеж (Кого? Чего?)
     */
    const CASE_GENITIVE = 1;

    /**
     * @const Дательный падеж (Кому? Чему?)
     */
    const CASE_DATIVE = 2;

    /**
     * @const Винительный падеж (Кого? Что?)
     */
    const CASE_ACCUSATIVE = 3;

    /**
     * @const Творительный падеж (Кем? Чем?)
     */
    const CASE_INSTRUMENTAL = 4;

    /**
     * @const Предложный падеж (О ком? О чем?)
     */
    const CASE_PREPOSITIONAL = 5;
    
    protected $cache = [];

    protected function sendRequest($function, $data)
    {
        $query = [];
        foreach ($data as $key => $item) {
            $query[] = urlencode($key)."=".urlencode($item);
        }
        $query = implode("&", $query);
        try {
            $url = "http://ws3.morpher.ru/russian/{$function}?{$query}";
            $h = md5($url);
            if (isset($this->cache[$h])) {
                return $this->cache[$h];
            } else {
                $cont = file_get_contents($url);
                if ($cont) {
                    $xml = simplexml_load_string($cont);
                    $this->cache[$h] = $xml;
                    return $xml;
                }
            }
        } catch (Exception $e) {
            return null;
        }
        return null;
    }

    public function inflect ($s, $case)
    {
        switch ($case) {
            case static::CASE_GENITIVE : $case = "Р"; break;
            case static::CASE_DATIVE : $case = "Д"; break;
            case static::CASE_ACCUSATIVE : $case = "В"; break;
            case static::CASE_INSTRUMENTAL : $case = "Т"; break;
            case static::CASE_PREPOSITIONAL : $case = "П"; break;
        }
        $xml = $this->sendRequest('declension', [
            "s" => $s,
        ]);

        if ($xml) {
            if (isset($xml->{$case})) {
                return strval($xml->{$case});
            }
        }
        return null;
    }

    public function getFullName($s)
    {
        $xml = $this->sendRequest('declension', [
            "s" => $s,
        ]);

        if ($xml) {
            if (isset($xml->{"ФИО"})) {
                return new FullName([
                    "surname" => strval($xml->{"ФИО"}->{"Ф"}),
                    "name" => strval($xml->{"ФИО"}->{"И"}),
                    "patronymic" => strval($xml->{"ФИО"}->{"О"}),
                ]);
            }
        }
        return null;
    }

    public function getStrNum($num, $unit, $case = 0)
    {
        if (!in_array($case, ["Р", "Д", "В", "Т", "П"])) {
            switch ($case) {
                case static::CASE_GENITIVE : $case = "Р"; break;
                case static::CASE_DATIVE : $case = "Д"; break;
                case static::CASE_ACCUSATIVE : $case = "В"; break;
                case static::CASE_INSTRUMENTAL : $case = "Т"; break;
                case static::CASE_PREPOSITIONAL : $case = "П"; break;
                default : $case = "И"; break;
            }
        }
        $xml = $this->sendRequest('spell', [
            "n" => $num,
            "unit" => $unit,
        ]);

        if ($xml) {
            if (isset($xml->{"n"}) && isset($xml->{"unit"})) {
                return new StrNum([
                    "unit" => strval($xml->{"unit"}->{$case}),
                    "value" => strval($xml->{"n"}->{$case}),
                ]);
            }
        }
        return null;
    }
}