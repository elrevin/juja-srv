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

class AddressData extends Object
{
    public $postalCode = '';
    public $country = '';
    public $region = '';
    public $regionType = '';
    public $regionTypeFull = '';
    public $area = '';
    public $areaType = '';
    public $areaTypeFull = '';
    public $settlement = '';
    public $settlementType = '';
    public $settlementTypeFull = '';
    public $street = '';
    public $streetType = '';
    public $streetTypeFull = '';
    public $house = '';
    public $houseType = '';
    public $houseTypeFull = '';
    public $block = '';
    public $blockType = '';
    public $blockTypeFull = '';
    public $fiasId = '';
    public $fiasLevel = '';
    public $kladrId = '';
    public $taxOffice = '';
    public $okato = '';
    public $oktmo = '';
    public $geoLat = '';
    public $geoLon = '';
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

    public function address($data, $first = true) {
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
        $result = file_get_contents($this->url."address", false, $context);
        $data = json_decode($result);
        $ret = null;
        if ($data->suggestions) {
            $ret = [];
            foreach ($data->suggestions as $item) {
                $item = $item->data;
                $retItem['postalCode'] = $item->postal_code;
                $retItem['country'] = $item->country;
                $retItem['region'] = $item->region;
                $retItem['regionType'] = $item->region_type;
                $retItem['regionTypeFull'] = $item->region_type_full;
                $retItem['area'] = $item->area;
                $retItem['areaType'] = $item->area_type;
                $retItem['areaTypeFull'] = $item->area_type_full;
                $retItem['settlement'] = $item->settlement;
                $retItem['settlementType'] = $item->settlement_type;
                $retItem['settlementTypeFull'] = $item->settlement_type_full;
                $retItem['street'] = $item->street;
                $retItem['streetType'] = $item->street_type;
                $retItem['streetTypeFull'] = $item->street_type_full;
                $retItem['house'] = $item->house;
                $retItem['houseType'] = $item->house_type;
                $retItem['houseTypeFull'] = $item->house_type_full;
                $retItem['block'] = $item->block;
                $retItem['blockType'] = $item->block_type;
                $retItem['blockTypeFull'] = $item->block_type_full;
                $retItem['fiasId'] = $item->fias_id;
                $retItem['fiasLevel'] = $item->fias_level;
                $retItem['kladrId'] = $item->kladr_id;
                $retItem['taxOffice'] = $item->tax_office;
                $retItem['okato'] = $item->okato;
                $retItem['oktmo'] = $item->oktmo;
                $retItem['geoLat'] = $item->geo_lat;
                $retItem['geoLon'] = $item->geo_lon;

                if ($first) {
                    return new AddressData($retItem);
                } else {
                    $ret[] = new AddressData($retItem);
                }
            }
        }
        return $ret;
    }

}