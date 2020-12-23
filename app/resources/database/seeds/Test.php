<?php

use dFramework\core\db\Seeder;
use dFramework\core\db\seeder\Faker;

class Test extends Seeder
{
    public function seed(Faker $faker) : Seeder
    {
        $this->table('membres', true)->columns([
            'nom' => $faker->firstName,
            'prenom' => $faker->lastName,
            'date_inscription' => $faker->date()
        ])->rows(50);

        return $this;
    }
}
