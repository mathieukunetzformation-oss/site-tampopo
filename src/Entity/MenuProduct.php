<?php

namespace App\Entity;

use App\Repository\MenuProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MenuProductRepository::class)]
class MenuProduct
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\Length(
        max: 80,
        maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $description = null;

    #[ORM\Column]
    private ?bool $isDisplayed = null;

    #[ORM\Column]
    private ?int $orderInCategory = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    private ?MenuCategory $category = null;

    #[ORM\Column(length: 255, nullable: true)]

    private ?string $photo = null;

    /**
     * @var Collection<int, ProductOffer>
     */
    #[ORM\OneToMany(targetEntity: ProductOffer::class, mappedBy: 'product', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $productOffers;

    public function __construct()
    {
        $this->productOffers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function isDisplayed(): ?bool
    {
        return $this->isDisplayed;
    }

    public function setIsDisplayed(bool $isDisplayed): static
    {
        $this->isDisplayed = $isDisplayed;

        return $this;
    }

    public function getOrderInCategory(): ?int
    {
        return $this->orderInCategory;
    }

    public function setOrderInCategory(int $orderInCategory): static
    {
        $this->orderInCategory = $orderInCategory;

        return $this;
    }

    public function getCategory(): ?MenuCategory
    {
        return $this->category;
    }

    public function setCategory(?MenuCategory $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    /**
     * @return Collection<int, ProductOffer>
     */
    public function getProductOffers(): Collection
    {
        return $this->productOffers;
    }

    public function addProductOffer(ProductOffer $productOffer): static
    {
        if (!$this->productOffers->contains($productOffer)) {
            $this->productOffers->add($productOffer);
            $productOffer->setProduct($this);
        }

        return $this;
    }

    public function removeProductOffer(ProductOffer $productOffer): static
    {
        if ($this->productOffers->removeElement($productOffer)) {
            // set the owning side to null (unless already changed)
            if ($productOffer->getProduct() === $this) {
                $productOffer->setProduct(null);
            }
        }

        return $this;
    }
}
