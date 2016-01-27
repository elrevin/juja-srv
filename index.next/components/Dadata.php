<?php
namespace app\components;
use yii\base\Component;
use yii\base\Object;
use yii\helpers\Json;

class CompanyData extends Object
{
    public $shortName = '';
    public $fullName = '';
    public $shortNameOpf = '';
    public $fullNameOpf = '';
    public $inn = '';
    public $kpp = '';
    public $ogrn = '';
    public $okved = '';
    public $okpo = '';
    public $address = '';
    public $directorName = '';
    public $directorPost = '';
}

class BankData extends Object
{
    public $shortName = '';
    public $fullName = '';
    public $inn = '';
    public $bic = '';
    public $swift = '';
    public $okpo = '';
    public $correspondentAccount = '';
}

class Dadata extends Component
{
    private $url,
        $token;
    public function init() {
        $this->url = "https://dadata.ru/api/v2/suggest/";
        $this->token = \Yii::$app->params['dadataApiKey'];
    }

    /**
     * @param string $data ИНН, ОГРН иои название компании
     * @param bool $first
     * @return CompanyData|CompanyData[]|null
     */
    public function party($data, $first = true) {
        $options = [
            'http' => [
                'method'  => 'POST',
                'header'  => [
                    'Content-type: application/json',
                    'Accept: application/json',
                    'Authorization: Token ' . $this->token,
                ],
                'content' => Json::encode([
                    "query" => $data,
                ]),
            ],
        ];
        $context = stream_context_create($options);
        $result = file_get_contents($this->url."party", false, $context);
        $data = json_decode($result);
        $ret = null;
        if ($data->suggestions) {
            $ret = [];
            foreach ($data->suggestions as $item) {
                $retItem['shortNameOpf'] = strval($item->data->name->short_with_opf);
                $retItem['fullNameOpf'] = strval($item->data->name->full_with_opf);
                $retItem['shortName'] = ($item->data->type == 'INDIVIDUAL' ? strval($item->data->name->full) : strval($item->data->name->short));
                $retItem['fullName'] = strval($item->data->name->full);
                $retItem['inn'] = strval($item->data->inn);
                $retItem['kpp'] = (isset($item->data->kpp) ? strval($item->data->kpp) : "");
                $retItem['ogrn'] = strval($item->data->ogrn);
                $retItem['okved'] = strval($item->data->okved);
                $retItem['okpo'] = strval($item->data->okpo);
                $retItem['address'] = strval($item->data->address->value);
                $retItem['directorName'] = ($item->data->type == 'INDIVIDUAL' ? strval($item->data->name->full) : strval($item->data->management->name));
                $retItem['directorPost'] = ($item->data->type == 'INDIVIDUAL' ? '' : strval($item->data->management->post));

                if ($first) {
                    return new CompanyData($retItem);
                } else {
                    $ret[] = new CompanyData($retItem);
                }
            }
        }
        return $ret;
    }

    /**
     * @param string $data bic или название название
     * @param bool $first
     * @return BankData|BankData[]|null
     */
    public function bank($data, $first = true) {
        $options = [
            'http' => [
                'method'  => 'POST',
                'header'  => [
                    'Content-type: application/json',
                    'Accept: application/json',
                    'Authorization: Token ' . $this->token,
                ],
                'content' => Json::encode([
                    "query" => $data,
                ]),
            ],
        ];
        $context = stream_context_create($options);
        $result = file_get_contents($this->url."bank", false, $context);
        $data = json_decode($result);
        $ret = null;
        if ($data->suggestions) {
            $ret = [];
            foreach ($data->suggestions as $item) {
                $retItem['shortName'] = strval($item->data->name->payment);
                $retItem['fullName'] = strval($item->data->name->full);

                $retItem['bic'] = strval($item->data->bic);
                $retItem['swift'] = strval($item->data->swift);
                $retItem['okpo'] = strval($item->data->okpo);
                $retItem['correspondentAccount'] = strval($item->data->correspondent_account);

                if ($first) {
                    return new BankData($retItem);
                } else {
                    $ret[] = new BankData($retItem);
                }
            }
        }
        return $ret;
    }
}