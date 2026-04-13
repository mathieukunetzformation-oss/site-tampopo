<?php

namespace App\DataFixtures;

use App\Entity\Reservation;
use App\Entity\Table;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\DBAL\Schema\Index;
use Doctrine\Persistence\ObjectManager;

class TableFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        for ($i = 1; $i < 27; $i++) {
            if ($i === 13) continue; //no table  13
            $table = new Table();
            $table->setNumber($i);
            $table->setSeats($this->determineSeats($i));
            $this->addReference('table_' . $i, $table); //we stock the created page to get it back in the other fixtures
            $manager->persist($table);
        }

        $manager->flush();
    }

    public function determineSeats(int $index): int
    {
        $numberOfSeats = 2;

        if ($index === 7) {
            $numberOfSeats = 3;
        }
        if ($index === 8) {
            $numberOfSeats = 4;
        }
        if ($index >= 20) {
            $numberOfSeats = 1;
        }

        return $numberOfSeats;
    }
}
