<?php

use Phinx\Migration\AbstractMigration;

class CreateTableProductCategories extends AbstractMigration
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
        $table = $this->table('product_categories');

        $table->addColumn('name', 'string', ['null' => false, 'limit' => 50]);
        $table->addColumn('is_active', 'boolean', ['null' => false, 'default' => '1']);
        $table->addColumn('created', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('created_by', 'integer', ['null' => true, 'limit' => 11]);
        $table->addColumn('updated', 'datetime', ['null' => true]);
        $table->addColumn('updated_by', 'integer', ['null' => true, 'limit' => 11]);

        $table->addIndex(['name'], ['name' => 'name', 'unique' => true]);
        $table->addIndex(['created'], ['name' => 'created']);

        $table->create();
    }

    public function down()
    {
        $table = $this->table('product_categories');

        $table->drop()->save();
    }
}
