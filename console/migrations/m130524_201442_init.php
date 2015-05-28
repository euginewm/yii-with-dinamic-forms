<?php

use yii\db\Schema;
use yii\db\Migration;

class m130524_201442_init extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%user}}', [
            'id' => Schema::TYPE_PK,
            'username' => Schema::TYPE_STRING . ' NOT NULL',
            'auth_key' => Schema::TYPE_STRING . '(32) NOT NULL',
            'password_hash' => Schema::TYPE_STRING . ' NOT NULL',
            'password_reset_token' => Schema::TYPE_STRING,
            'email' => Schema::TYPE_STRING . ' NOT NULL',

            'status' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 10',
            'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
            'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
        ], $tableOptions);

        // START: Tables for i18n translations 
        $this->createTable('{{%source_message}}', [
            'id' => Schema::TYPE_PK,
            'category' => Schema::TYPE_STRING . '(32) NOT NULL',
            'message'  => Schema::TYPE_TEXT,
        ], $tableOptions);

        $this->createTable('{{%message}}', [
            'id' => Schema::TYPE_INTEGER,
            'language' => Schema::TYPE_STRING . '(16)',
            'translation'  => Schema::TYPE_TEXT,
            'PRIMARY KEY (id, language)',
            'CONSTRAINT fk_message_source_message FOREIGN KEY (id) REFERENCES {{%source_message}} (id) ON DELETE CASCADE ON UPDATE RESTRICT',

        ], $tableOptions);
        // END: Tables for i18n translations 

        // Session table
        $sessionDataFieldType = Schema::TYPE_BINARY;
        switch ($this->db->driverName) {
            case 'mysql': $sessionDataFieldType = 'longblob'; break;
            case 'pgsql': $sessionDataFieldType = 'bytea'; break;
            case 'mssql': $sessionDataFieldType = 'blob'; break;
        }

        $this->createTable('{{%session}}', [
            'id' => Schema::TYPE_STRING . '(64) NOT NULL',
            'expire' => Schema::TYPE_INTEGER,
            'data'  => $sessionDataFieldType,
        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%user}}');
        $this->dropTable('{{%source_message}}');
        $this->dropTable('{{%message}}');
        $this->dropTable('{{%session}}');
    }
}
