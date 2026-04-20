<?php

namespace App\Tests\Entity;

use App\Entity\MenuProduct;
use App\Entity\ProductOffer;
use PHPUnit\Framework\TestCase;

class MenuProductTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $product = new MenuProduct();

        $product->setTitle('Ramen')
            ->setDescription('Un bouillon salée avec du porc')
            ->setIsDisplayed(true)
            ->setOrderInCategory(10000);

        $this->assertSame('Ramen', $product->getTitle());
        $this->assertSame('Un bouillon salée avec du porc', $product->getDescription());
        $this->assertTrue($product->isDisplayed());
        $this->assertSame(10000, $product->getOrderInCategory());
    }

    public function testProductOffersCollection(): void
    {
        $product = new MenuProduct();
        $offer = new ProductOffer();

        //test add offer
        $product->addProductOffer($offer);
        $this->assertCount(1, $product->getProductOffers());
        $this->assertTrue($product->getProductOffers()->contains($offer));
        $this->assertSame($product, $offer->getProduct());

        //test delete offer
        $product->removeProductOffer($offer);
        $this->assertCount(0, $product->getProductOffers());
        $this->assertNull($offer->getProduct());
    }

    //test voluntary failing
    public function testOrderInCategoryShouldBePositive(): void
    {
        $product = new MenuProduct();
        $product->setOrderInCategory(-1);

        $this->assertGreaterThanOrEqual(0, $product->getOrderInCategory());
    }
}
