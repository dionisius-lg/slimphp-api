<?php


use Phinx\Seed\AbstractSeed;

class UserLevels extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run()
    {
        $table = $this->table('user_levels');

        $data = [
            [
                'name' => 'Admin',
                'created_by' => 1,
            ],
        ];

        $table->insert($data)->save();
    }
}
