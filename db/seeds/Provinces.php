<?php


use Phinx\Seed\AbstractSeed;

class Provinces extends AbstractSeed
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
        $table = $this->table('provinces');

        $data = [
            [
                'id' => '1',
                'name' => 'Aceh',
                'created_by' => '1',
            ],
            [
                'id' => '2',
                'name' => 'Sumatera Barat',
                'created_by' => '1',
            ],
            [
                'id' => '3',
                'name' => 'Maluku',
                'created_by' => '1',
            ],
            [
                'id' => '4',
                'name' => 'Bali',
                'created_by' => '1',
            ],
            [
                'id' => '5',
                'name' => 'Kalimantan Timur',
                'created_by' => '1',
            ],
            [
                'id' => '6',
                'name' => 'Jawa Barat',
                'created_by' => '1',
            ],
            [
                'id' => '7',
                'name' => 'Kep. Bangka Belitung',
                'created_by' => '1',
            ],
            [
                'id' => '8',
                'name' => 'Jawa Timur',
                'created_by' => '1',
            ],
            [
                'id' => '9',
                'name' => 'Riau',
                'created_by' => '1',
            ],
            [
                'id' => '10',
                'name' => 'Kalimantan Selatan',
                'created_by' => '1',
            ],
            [
                'id' => '11',
                'name' => 'Jawa Tengah',
                'created_by' => '1',
            ],
            [
                'id' => '12',
                'name' => 'Yogyakarta',
                'created_by' => '1',
            ],
            [
                'id' => '13',
                'name' => 'Kepulauan Riau',
                'created_by' => '1',
            ],
            [
                'id' => '14',
                'name' => 'Jambi',
                'created_by' => '1',
            ],
            [
                'id' => '15',
                'name' => 'Sumatera Utara',
                'created_by' => '1',
            ],
            [
                'id' => '16',
                'name' => 'Kalimantan Barat',
                'created_by' => '1',
            ],
            [
                'id' => '17',
                'name' => 'Bengkulu',
                'created_by' => '1',
            ],
            [
                'id' => '18',
                'name' => 'Nusa Tenggara Barat',
                'created_by' => '1',
            ],
            [
                'id' => '19',
                'name' => 'Gorontalo',
                'created_by' => '1',
            ],
            [
                'id' => '20',
                'name' => 'Banten',
                'created_by' => '1',
            ],
            [
                'id' => '21',
                'name' => 'Jakarta',
                'created_by' => '1',
            ],
            [
                'id' => '22',
                'name' => 'Papua',
                'created_by' => '1',
            ],
            [
                'id' => '23',
                'name' => 'Sulawesi Tenggara',
                'created_by' => '1',
            ],
            [
                'id' => '24',
                'name' => 'Sumatera Selatan',
                'created_by' => '1',
            ],
            [
                'id' => '25',
                'name' => 'Nusa Tenggara Timur',
                'created_by' => '1',
            ],
            [
                'id' => '26',
                'name' => 'Lampung',
                'created_by' => '1',
            ],
            [
                'id' => '27',
                'name' => 'Sulawesi Selatan',
                'created_by' => '1',
            ],
            [
                'id' => '28',
                'name' => 'Sulawesi Utara',
                'created_by' => '1',
            ],
            [
                'id' => '29',
                'name' => 'Papua Barat',
                'created_by' => '1',
            ],
            [
                'id' => '30',
                'name' => 'Sulawesi Tengah',
                'created_by' => '1',
            ],
            [
                'id' => '31',
                'name' => 'Kalimantan Utara',
                'created_by' => '1',
            ],
            [
                'id' => '32',
                'name' => 'Kalimantan Tengah',
                'created_by' => '1',
            ],
            [
                'id' => '33',
                'name' => 'Maluku Utara',
                'created_by' => '1',
            ],
            [
                'id' => '34',
                'name' => 'Sulawesi Barat',
                'created_by' => '1',
            ],
            [
                'id' => '35',
                'name' => '-',
                'created_by' => '1',
            ],
        ];

        $table->insert($data)->save();
    }
}
