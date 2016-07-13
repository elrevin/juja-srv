<?php
namespace app\base\db;
/**
 * 
 * Класс используется для создания так называемых расширенных моделей
 * 
 * @package app\base\db
 */
class ExtendedActiveRecord extends ActiveRecord
{
    /**
     * Здесь отличие от ActiveRecord только одно - тип поля inherited, который указывает на одноименные поля, которые нужно взять с наследуемой модели
     * При описании такого поля игнорируется тип даннх и все что с ним связано (relativeModel, selectOptions и пр.), но можно переопределить условия отображения 
     * и прочие настройки, не связанные с типом данных. Имена поля inherited и поля оригинала должны совпадать.
     * 
     * @var array
     */
    static protected $structure = [];
    
    /**
     *  Полное имя класса модели, которую раширяем
     * 
     * @var string
     */
    static protected $extendedModel = '';
    
}