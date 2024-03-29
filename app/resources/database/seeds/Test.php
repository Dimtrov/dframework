<?php

use dFramework\core\db\Seeder;
use dFramework\core\db\seeder\Faker;

class Test extends Seeder
{
    public function seed(Faker $faker) : Seeder
    {
        $this->table('etudiants', true)->columns([
            'nom' => $faker->firstName,
            'sexe' => $faker->randomElement(['m', 'f']),
            'date_naissance' => $faker->date()
        ])->rows(50);

        return $this;
    }
}
