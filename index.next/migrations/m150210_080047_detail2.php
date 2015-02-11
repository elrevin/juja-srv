<?php

use yii\db\Schema;
use yii\db\Migration;

class m150210_080047_detail2 extends Migration
{
    public function up()
    {
		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
		}

		$this->createTable('{{%news}}', [
			'id' => Schema::TYPE_PK,
			'hidden' => Schema::TYPE_SMALLINT,
			'del' => Schema::TYPE_SMALLINT,
			'title' => Schema::TYPE_STRING . ' NOT NULL',
			'anons' => Schema::TYPE_TEXT . ' NOT NULL',
			'content' => Schema::TYPE_TEXT . ' NOT NULL',
			'publish_date' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
		], $tableOptions);

		$this->createTable('{{%news_files}}', [
			'id' => Schema::TYPE_PK,
			'file' => Schema::TYPE_INTEGER,
			'master_table_id' => Schema::TYPE_INTEGER,
		], $tableOptions);

		$this->createIndex('FK_news_files', '{{%news_files}}', 'master_table_id');
		$this->addForeignKey(
			'FK_news_files', '{{%news_files}}', 'master_table_id', '{{%news}}', 'id', 'SET NULL', 'CASCADE'
		);
    }

    public function down()
    {
        $this->dropTable('{{%news}}');
        $this->dropTable('{{%news_files}}');
    }
}
