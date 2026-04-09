<?php

namespace App\DataFixtures;

use App\Entity\MenuPage;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class MenuPageFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {

        $pages = [
            'Vocabulaire',
            'Ramen',
            'Autres Plats',
            'Page de Stockage'
        ];

        foreach ($pages as $index => $title) {

            $page = new MenuPage();
            $page->setTitle($title);
            $page->setTitleIsDisplayed(false); //by default the title is not displayed
            $page->setPageOrder(($index + 1) * 10);
            $page->setPageIsDisplayed($index === 3 ? false : true); //by default stock page is not displayed to the clients
            $page->setIsProtected($index === 3 ? true : false); //by default stock page is protected and cannot be deletedS
            $manager->persist($page);

            $this->addReference('menu_page_' . $index, $page); //we stock the created page to get it back in the other fixtures
        }

        $manager->flush();
    }
}
