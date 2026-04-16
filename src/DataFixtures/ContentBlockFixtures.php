<?php

namespace App\DataFixtures;

use App\Entity\ContentBlock;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ContentBlockFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $welcomeMsgBlock = new ContentBlock();
        $welcomeMsgBlock->setName("Message d'accueil");
        $welcomeMsgBlock->setType("text");
        $welcomeMsgBlock->setTextContent("Nous serons fermés du 20 au 25 mars.");
        $manager->persist($welcomeMsgBlock);

        $hoursBlock = new ContentBlock();
        $hoursBlock->setName("Horaires");
        $hoursBlock->setType("text");
        $hoursBlock->setTextContent("Du Mardi au Samedi:
					<br>
					12h00-14h30<br>
					19h00-21h30</p>");
        $manager->persist($hoursBlock);

        $phoneBlock = new ContentBlock();
        $phoneBlock->setName("Téléphone");
        $phoneBlock->setType("text");
        $phoneBlock->setTextContent("05 64 72 42 35");
        $manager->persist($phoneBlock);

        $adressBlock = new ContentBlock();
        $adressBlock->setName("Adresse");
        $adressBlock->setType("text");
        $adressBlock->setTextContent("5 rue Thiers, 17000 La Rochelle");
        $manager->persist($adressBlock);

        $manager->flush();
    }
}
