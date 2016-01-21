<?php

use yii\db\Migration;

class m160104_090003_addSRegistry extends Migration
{
    public function up()
    {
        $this->createTable("s_registry", [
            "id" => "pk",
            "module" => "varchar(1024) not null default ''",
            "key" => "varchar(1024) not null default ''",
            "val" => "varchar(1024) not null default ''",
        ]);
    }

    public function down()
    {
        echo "m160104_090003_addSRegistry cannot be reverted.\n";

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
