<?php

namespace App\DataFixtures;

use App\Entity\MenuCategory;
use App\Entity\MenuPage;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class MenuCategoryFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $categories = [
            'Le ramen en quelques mots',
            'Quelques termes pour mieux savourer votre ramen',
            'Ramen',
            'Okazu',
            'Donburi',
            'Catégorie de Stockage'
        ];

        $pageMapping = [2, 1, 2, 1]; //how many category per page
        $categoryCounter = 0;

        foreach ($pageMapping as $index => $numberOfCategory) {

            for ($i = 0; $i < $numberOfCategory; $i++) {
                $category = new MenuCategory();
                $categoryTitle = $categories[$categoryCounter];
                $category->setTitle($categoryTitle);
                $category->setDescription("Ceci est un exemple de description pour la catégorie " . $categoryTitle);
                $category->setDisplayAsGrid(false);
                $category->setOrderInPage(($i + 1) * 10); // start order at 10
                $category->setCategoryIsDisplayed($categoryCounter === 5 ? false : true);
                $category->setPage($this->getReference('menu_page_' . $index, MenuPage::class));
                $category->setIsProtected($categoryCounter === 5 ? true : false); //by default stock category is protected and cannot be deleted

                $manager->persist($category);

                $this->addReference('menu_category_' . $categoryCounter, $category);


                $categoryCounter++;
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [MenuPageFixtures::class];
    }
}
