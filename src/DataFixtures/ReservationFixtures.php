<?php

namespace App\DataFixtures;

use App\Entity\Reservation;
use App\Entity\Table;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ReservationFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $firstNames = ['Alice', 'Bob', 'Charlie', 'Diana', 'Ethan', 'Fiona'];
        $surnames = ['Smith', 'Johnson', 'Brown', 'Taylor', 'Lee', 'Walker'];

        for ($i = 1; $i <= 26; $i++) {
            if ($i === 13) continue; // skip table 13

            $table = $this->getReference(
                'table_' . $i,
                Table::class
            );

            $numReservations = rand(1, 2);

            for ($r = 0; $r < $numReservations; $r++) {
                $reservation = new Reservation();

                $reservation->setFirstname($firstNames[array_rand($firstNames)]);
                $reservation->setSurname($surnames[array_rand($surnames)]);
                $reservation->setPartySize(rand(1, $table->getSeats())); // can't exceed table seats
                $reservation->setPhoneNumber('06' . rand(10000000, 99999999));
                $reservation->setEmail('guest' . $i . '-' . $r . '@example.com');

                $date = new \DateTime();
                $date->modify('+' . rand(0, 30) . ' days'); // Random future date and time
                $reservation->setDate($date);

                $time = new \DateTime();
                $time->setTime(rand(12, 20), [0, 15, 30, 45][rand(0, 3)]); // random 12:00-20:45
                $reservation->setTime($time);

                $reservation->addReservedTable($table);

                $manager->persist($reservation);
            }


            $manager->flush();
        }
    }

    public function getDependencies(): array
    {
        return [TableFixtures::class,];
    }
}
