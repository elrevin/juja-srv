<?php

use yii\db\Schema;
use yii\db\Migration;

class m150228_210419_init extends Migration
{
    public function up()
    {
        $this->createTable("s_config", [
            'id' => 'pk',
            'title' => "varchar(1024) not null default ''",
            'name' => "varchar(1024) not null default ''",
            'type' => "varchar(1024) not null default 'string'",
            'value' => "text default null",
        ]);

        $this->createTable("s_users_groups", [
            'id' => 'pk',
            'title' => "varchar(1024) not null default ''",
            'cp_access' => "tinyint(1) not null default 0",
        ]);

        $this->insert("s_users_groups", [
            "title" => "Администраторы",
            "cp_access" => 1
        ]);

        $groupId = Yii::$app->db->getLastInsertID();

        $this->createTable("s_users", [
            'id' => 'pk',
            'username' => "varchar(1024) not null default ''",
            'password' => "varchar(1024) not null default ''",
            'group_id' => "int(11) not null",
            'name' => "varchar(1024) not null default ''",
            'hash' => "varchar(1024) not null default ''",
            'email' => "varchar(1024) not null default ''",
            'last_login' => "datetime default null",
            'last_action' => "datetime default null",
            'restore_code' => "varchar(1024) default null",
            'restore_code_expires' => "datetime default null",
            'su' => "tinyint(1) not null default 0",
        ]);
        $this->addForeignKey("s_users__group_id__s_users_groups__id", "s_users", "group_id", "s_users_groups", "id", "CASCADE", "CASCADE");

        $this->insert("s_users", [
            "username" => "root",
            "password" => '',
            "group_id" => $groupId,
            "name" => "Администратор",
            "email" => "mail@localhost",
            "su" => 1,
        ]);

        $this->createTable("s_rights_rules", [
            'id' => 'pk',
            'model_name' => "varchar(1024) not null default ''",
            'user_group_id' => "int(11) default null",
            'user_id' => "int(11) default null",
            'rights' => "tinyint(1) not null default 0",
        ]);

        $this->addForeignKey("s_rights_rules__user_group_id__s_users_groups__id", "s_rights_rules", "user_group_id", "s_users_groups", "id", "CASCADE", "CASCADE");
        $this->addForeignKey("s_rights_rules__user_id__s_users__id", "s_rights_rules", "user_id", "s_users", "id", "CASCADE", "CASCADE");

    }

    public function down()
    {
        echo "m150228_210419_init cannot be reverted.\n";

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
