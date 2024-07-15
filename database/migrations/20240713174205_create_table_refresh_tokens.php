<?php

use Phinx\Migration\AbstractMigration;

class CreateTableRefreshTokens extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    addCustomColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Any other destructive changes will result in an error when trying to
     * rollback the migration.
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function up()
    {
        $table = $this->table('refresh_tokens');

        $table->addColumn('user_id', 'integer', ['null' => true, 'limit' => 11]);
        $table->addColumn('user_agent', 'string', ['null' => true, 'limit' => 255]);
        $table->addColumn('ip_address', 'string', ['null' => true, 'limit' => 20]);
        $table->addColumn('token', 'text', ['null' => true]);
        $table->addColumn('expired', 'datetime', ['null' => true]);
        $table->addColumn('created', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('updated', 'datetime', ['null' => true, 'update' => 'CURRENT_TIMESTAMP']);

        $table->addIndex(['user_id'], ['name' => 'user_id', 'unique' => true]);

        $table->create();
    }

    public function down()
    {
        $table = $this->table('refresh_tokens');

        $table->drop()->save();
    }
}
