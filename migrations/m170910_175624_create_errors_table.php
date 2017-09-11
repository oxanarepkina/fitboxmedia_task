<?php

use yii\db\Migration;

/**
 * Handles the creation of table `errors`.
 */
class m170910_175624_create_errors_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('errors', [
            'id' => $this->primaryKey(),
            'ip' => $this->string()->notNull(),
            'date' => $this->dateTime()->notNull(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('errors');
    }
}
