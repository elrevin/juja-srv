<?php

use yii\db\Schema;
use yii\db\Migration;

class m150529_044110_fieldsHistory extends Migration
{
    public function up()
    {
        $this->createTable("s_data_history_events", [
            "id" => "pk",
            "time" => "datetime DEFAULT NULL",
            "user_id" => "int(11) default null",
            "ip" => "varchar(1024) not null default ''",
            "event" => "varchar(1024) not null default ''",
            "model" => "varchar(1024) not null default ''",
            "record_id" => "int(11) default null",
        ]);

        $this->addForeignKey("s_data_history_events__user_id", "s_data_history_events", "user_id", "s_users", "id", "CASCADE", "CASCADE");

        $this->createTable("s_data_history", [
            "id" => "pk",
            "event_id" => "int(11) default null",
            "field" => "varchar(1024) not null default ''",
            "value" => "longtext",
        ]);

        $this->addForeignKey("s_data_history__event_id", "s_data_history", "event_id", "s_data_history_events", "id", "CASCADE", "CASCADE");
    }

    public function down()
    {
        echo "m150529_044110_fieldsHistory cannot be reverted.\n";

        return false;
    }
    
    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }
    
    public function safeDown()
    {
    }
    */
}
