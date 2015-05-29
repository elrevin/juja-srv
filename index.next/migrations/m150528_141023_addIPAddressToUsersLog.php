<?php

use yii\db\Schema;
use yii\db\Migration;

class m150528_141023_addIPAddressToUsersLog extends Migration
{
    public function up()
    {
        $this->addColumn("s_users_log", "ip", "varchar(1024) not null default ''");
    }

    public function down()
    {
        echo "m150528_141023_addIPAddressToUsersLog cannot be reverted.\n";

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
