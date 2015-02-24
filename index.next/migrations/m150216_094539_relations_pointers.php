<?php

use yii\db\Schema;
use yii\db\Migration;

class m150216_094539_relations_pointers extends Migration
{
    public function up()
    {
        $this->addColumn('{{%news}}', 'module',  Schema::TYPE_INTEGER);
        $this->addColumn('{{%news}}', 'template',  Schema::TYPE_INTEGER);
        $this->addColumn('{{%goods}}', 'module',  Schema::TYPE_INTEGER);
    }

    public function down()
    {
        $this->dropColumn('{{%news}}', 'module');
        $this->dropColumn('{{%news}}', 'template');
        $this->dropColumn('{{%goods}}', 'module');
    }
}
