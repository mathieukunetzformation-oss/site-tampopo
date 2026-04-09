<?php

namespace App\Entity;

use App\Repository\MenuPageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MenuPageRepository::class)]
class MenuPage
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

    #[ORM\Column]
    private ?bool $titleIsDisplayed = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: "La description ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?int $pageOrder = null;

    #[ORM\Column]
    private ?bool $pageIsDisplayed = null;

    /**
     * @var Collection<int, MenuCategory>
     */
    #[ORM\OneToMany(targetEntity: MenuCategory::class, mappedBy: 'page')]
    #[ORM\OrderBy(['orderInPage' => 'ASC'])]
    private Collection $categories;

    #[ORM\Column]
    private ?bool $isProtected = null;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
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

    public function getPageOrder(): ?int
    {
        return $this->pageOrder;
    }

    public function setPageOrder(?int $pageOrder): static
    {
        $this->pageOrder = $pageOrder;

        return $this;
    }

    /**
     * @return Collection<int, MenuCategory>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(MenuCategory $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->setPage($this);
        }

        return $this;
    }

    public function removeCategory(MenuCategory $category): static
    {
        if ($this->categories->removeElement($category)) {
            // set the owning side to null (unless already changed)
            if ($category->getPage() === $this) {
                $category->setPage(null);
            }
        }

        return $this;
    }

    public function isTitleIsDisplayed(): ?bool
    {
        return $this->titleIsDisplayed;
    }

    public function setTitleIsDisplayed(bool $titleIsDisplayed): static
    {
        $this->titleIsDisplayed = $titleIsDisplayed;

        return $this;
    }

    public function isPageIsDisplayed(): ?bool
    {
        return $this->pageIsDisplayed;
    }

    public function setPageIsDisplayed(bool $pageIsDisplayed): static
    {
        $this->pageIsDisplayed = $pageIsDisplayed;

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
