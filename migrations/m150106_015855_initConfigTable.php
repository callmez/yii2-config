<?php

use yii\db\Schema;
use yii\db\Migration;

class m150106_015855_initConfigTable extends Migration
{
    public function up()
    {
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        } else {
            $tableOptions = null;
        }
        $this->createTable('{{%config}}', [
            'name' => Schema::TYPE_STRING . "(64) NOT NULL COMMENT 'Config Name'",
            'value' => Schema::TYPE_TEXT . " NOT NULL COMMENT 'Config Values'",
            'PRIMARY KEY (name)'
        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%config}}');
    }
}
