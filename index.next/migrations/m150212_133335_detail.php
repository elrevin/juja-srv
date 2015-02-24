<?php

use yii\db\Schema;
use yii\db\Migration;

class m150212_133335_detail extends Migration
{
    public function up()
    {
		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
		}

		$this->createTable('{{%news}}', [
			'id' => Schema::TYPE_PK,
			'title' => Schema::TYPE_STRING . ' NOT NULL',
			'anons' => Schema::TYPE_TEXT . ' NOT NULL',
			'content' => Schema::TYPE_TEXT . ' NOT NULL',
			'publish_date' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
		], $tableOptions);

		$this->createTable('{{%news_tags}}', [
			'id' => Schema::TYPE_PK,
			'tag' => Schema::TYPE_INTEGER,
			'master_table_id' => Schema::TYPE_INTEGER,
		], $tableOptions);

		$this->createIndex('FK_news_tags', '{{%news_tags}}', 'master_table_id');
		$this->addForeignKey(
			'FK_news_tags', '{{%news_tags}}', 'master_table_id', '{{%news}}', 'id', 'SET NULL', 'CASCADE'
		);
		
		$this->createTable('{{%tags}}', [
			'id' => Schema::TYPE_PK,
			'title' => Schema::TYPE_STRING
		], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%news}}');
        $this->dropTable('{{%news_tags}}');
        $this->dropTable('{{%tags}}');
    }
}
