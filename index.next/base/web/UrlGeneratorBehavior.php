<?php

namespace app\base\web;

use yii\base\Behavior;

class UrlGeneratorBehavior extends Behavior
{
    public $in_attribute = 'title';
    public $out_attribute = 'url';
    public $translit = true;

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'getUrl'
        ];
    }

    public function getUrl( $event )
    {
        if ( empty( $this->owner->{$this->out_attribute} ) ) {
            $this->owner->{$this->out_attribute} = $this->generateUrl( $this->owner->{$this->in_attribute} );
        } else {
            $this->owner->{$this->out_attribute} = $this->generateUrl( $this->owner->{$this->out_attribute} );
        }
    }

    private function generateUrl( $url )
    {
        $url = $this->generate( $url );
        if ( $this->checkUnique( $url ) ) {
            return $url;
        } else {
            for ( $suffix = 2; !$this->checkUnique( $new_url = $url . '-' . $suffix ); $suffix++ ) {}
            return $new_url;
        }
    }

    private function generate( $url )
    {
        if ( $this->translit ) {
            return Inflector::slug( TransliteratorHelper::process( $url ), '-', true );
        } else {
            return $this->url( $url, '-', true );
        }
    }

    private function url( $string, $replacement = '-', $lowercase = true )
    {
        $string = preg_replace( '/[^\p{L}\p{Nd}]+/u', $replacement, $string );
        $string = trim( $string, $replacement );
        return $lowercase ? strtolower( $string ) : $string;
    }

    private function checkUnique( $url )
    {
        $pk = $this->owner->primaryKey();
        $pk = $pk[0];

        $condition = $this->out_attribute . ' = :out_attribute';
        $params = [ ':out_attribute' => $url ];
        if ( !$this->owner->isNewRecord ) {
            $condition .= ' and ' . $pk . ' != :pk';
            $params[':pk'] = $this->owner->{$pk};
        }

        return !$this->owner->find()
            ->where( $condition, $params )
            ->one();
    }
}