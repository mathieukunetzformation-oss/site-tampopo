<?php

namespace App\DataFixtures;

use App\Entity\MenuCategory;
use App\Entity\MenuProduct;
use App\Entity\ProductOffer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class MenuProductFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $categoryMapping = [1, 6, 5, 3, 2]; // how many product per category
        $productCounter = 0;
        foreach ($categoryMapping as $index => $numberOfProducts) {

            for ($i = 0; $i < $numberOfProducts; $i++) {
                $product = new MenuProduct();
                $productTitle = "Produit Exemple " . $productCounter;
                $product->setTitle($productTitle);
                $product->setDescription("Ceci est un exemple de description pour le produit " . $productTitle);
                $product->setOrderInCategory(($i + 1) * 10);
                $product->setIsDisplayed($index >= 4 ? false : true);
                $product->setCategory(
                    $this->getReference(
                        'menu_category_' . $index,
                        MenuCategory::class
                    )
                );

                $productOffer = new ProductOffer();
                $productOffer->setPrice("10.50");
                $productOffer->setQuantity(1);
                $productOffer->setProduct($product);

                $product->addProductOffer($productOffer);

                $manager->persist($productOffer);
                $manager->persist($product);

                $productCounter++;
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [MenuCategoryFixtures::class];
    }
}
