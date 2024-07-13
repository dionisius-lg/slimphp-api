<?php


use Phinx\Seed\AbstractSeed;

class Users extends AbstractSeed
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
        $table = $this->table('users');

        $data = [
            [
                'username' => 'dion',
                'password' => password_hash('dion', PASSWORD_BCRYPT, ['cost' => 10]),
                'email' => 'test@email.com',
                'user_level_id' => '1',
                'created_by' => '1',
            ],
        ];

        $table->insert($data)->save();
    }
}
