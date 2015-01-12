<?php

use yii\db\Schema;
use app\components\Migration;

class m150106_015855_initConfigTable extends Migration
{
    public function up()
    {
        $this->createTable('{{%config}}', [
            'name' => Schema::TYPE_STRING . "(64) NOT NULL COMMENT 'Config Name'",
            'value' => Schema::TYPE_TEXT . " NOT NULL COMMENT 'Config Values'",
            'PRIMARY KEY (name)'
        ], $this->tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%config}}');
    }
}
