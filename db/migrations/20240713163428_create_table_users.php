<?php

use Phinx\Migration\AbstractMigration;

class CreateTableUsers extends AbstractMigration
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
        $table = $this->table('users');

        $table->addColumn('username', 'string', ['null' => false, 'limit' => 50]);
        $table->addColumn('password', 'string', ['null' => false, 'limit' => 255]);
        $table->addColumn('fullname', 'string', ['null' => true, 'limit' => 100]);
        $table->addColumn('email', 'string', ['null' => true, 'limit' => 100]);
        $table->addColumn('birth_place', 'string', ['null' => true, 'limit' => 50]);
        $table->addColumn('birth_date', 'date', ['null' => true]);
        $table->addColumn('phone', 'string', ['null' => true, 'limit' => 25]);
        $table->addColumn('address', 'string', ['null' => true, 'limit' => 255]);
        $table->addColumn('zip_code', 'string', ['null' => true, 'limit' => 6]);
        $table->addColumn('province_id', 'integer', ['null' => true, 'limit' => 11]);
        $table->addColumn('city_id', 'integer', ['null' => true, 'limit' => 11]);
        $table->addColumn('user_level_id', 'integer', ['null' => true, 'limit' => 11]);
        $table->addColumn('is_active', 'boolean', ['null' => false, 'default' => '1']);
        $table->addColumn('created', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('created_by', 'integer', ['null' => true, 'limit' => 11]);
        $table->addColumn('updated', 'datetime', ['null' => true]);
        $table->addColumn('updated_by', 'integer', ['null' => true, 'limit' => 11]);

        $table->addIndex(['username'], ['name' => 'username', 'unique' => true]);
        $table->addIndex(['email'], ['name' => 'email', 'unique' => true]);
        $table->addIndex(['fullname'], ['name' => 'fullname']);
        $table->addIndex(['province_id'], ['name' => 'province_id']);
        $table->addIndex(['city_id'], ['name' => 'city_id']);
        $table->addIndex(['user_level_id'], ['name' => 'user_level_id']);
        $table->addIndex(['created'], ['name' => 'created']);

        $table->create();
    }

    public function down()
    {
        $table = $this->table('users');

        $table->drop()->save();
    }
}
