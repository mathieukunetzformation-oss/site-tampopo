<?php

namespace App\Entity;

use App\Repository\MenuCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MenuCategoryRepository::class)]
class MenuCategory
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
    private ?int $orderInPage = null;

    #[ORM\Column]
    private ?bool $categoryIsDisplayed = null;

    /**
     * @var Collection<int, MenuProduct>
     */
    #[ORM\OneToMany(targetEntity: MenuProduct::class, mappedBy: 'category')]
    #[ORM\OrderBy(['orderInCategory' => 'ASC'])]
    private Collection $products;

    #[ORM\ManyToOne(inversedBy: 'categories')]
    #[ORM\JoinColumn(nullable: false)]
    private ?MenuPage $page = null;

    #[ORM\Column]
    private ?bool $displayAsGrid = null;

    #[ORM\Column]
    private ?bool $isProtected = null;

    public function __construct()
    {
        $this->products = new ArrayCollection();
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

    public function getOrderInPage(): ?int
    {
        return $this->orderInPage;
    }

    public function setOrderInPage(int $orderInPage): static
    {
        $this->orderInPage = $orderInPage;

        return $this;
    }

    /**
     * @return Collection<int, MenuProduct>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(MenuProduct $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->setCategory($this);
        }

        return $this;
    }

    public function removeProduct(MenuProduct $product): static
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getCategory() === $this) {
                $product->setCategory(null);
            }
        }

        return $this;
    }

    public function getPage(): ?MenuPage
    {
        return $this->page;
    }

    public function setPage(?MenuPage $page): static
    {
        $this->page = $page;

        return $this;
    }

    public function isCategoryIsDisplayed(): ?bool
    {
        return $this->categoryIsDisplayed;
    }

    public function setCategoryIsDisplayed(bool $categoryIsDisplayed): static
    {
        $this->categoryIsDisplayed = $categoryIsDisplayed;

        return $this;
    }

    public function isDisplayAsGrid(): ?bool
    {
        return $this->displayAsGrid;
    }

    public function setDisplayAsGrid(bool $displayAsGrid): static
    {
        $this->displayAsGrid = $displayAsGrid;

        return $this;
    }

    public function isProtected(): ?bool
    {
        return $this->isProtected;
    }

    public function setIsProtected(bool $isProtected): static
    {
        $this->isProtected = $isProtected;

        return $this;
    }
}
