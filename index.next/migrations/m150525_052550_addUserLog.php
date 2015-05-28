<?php

use yii\db\Schema;
use yii\db\Migration;

class m150525_052550_addUserLog extends Migration
{
    public function up()
    {
        $this->createTable('s_users_log', [
            "id" => "pk",
            "user_id" => "int(11) NOT NULL",
            "time" => "datetime NOT NULL DEFAULT CURRENT_TIMESTAMP",
            "event" => "varchar(1024) default null",
        ]);
        $this->addForeignKey("s_users_log__user_id", "s_users_log", "user_id", "s_users", "id", "CASCADE", "CASCADE");
    }

    public function down()
    {
        echo "m150525_052550_addUserLog cannot be reverted.\n";

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
