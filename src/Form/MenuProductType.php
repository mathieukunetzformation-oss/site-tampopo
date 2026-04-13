<?php

namespace App\Form;

use App\Entity\MenuCategory;
use App\Entity\MenuProduct;
use App\Entity\ProductOffer;
use App\Repository\MenuCategoryRepository;
use App\Repository\MenuProductRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints as Assert;

class MenuProductType extends AbstractType
{

    private MenuProductRepository $repo;
    private MenuCategoryRepository $categoryRepo;

    public function __construct(MenuProductRepository $repo, MenuCategoryRepository $categoryRepo)
    {
        $this->repo = $repo;
        $this->categoryRepo = $categoryRepo;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, array_merge([
                'label' => 'Nom du produit',
            ], $this->getFieldClasses('title')))

            ->add('description', TextareaType::class, array_merge([
                'label' => 'Description du produit',
                'required' => false,
            ], $this->getFieldClasses('description')))

            ->add('productOffers', CollectionType::class, array_merge([
                'entry_type' => ProductOfferType::class,
                'allow_add' => true, //if unrecognized items are submitted to the collection, they will be added as new items
                'allow_delete' => true, //if an existing item is not contained in the submitted data, it will be correctly absent from the final array of items
                'by_reference' => false, // IMPORTANT
                'prototype' => true,
                'label' => "Offres",

            ], $this->getFieldClasses('offers')))

            ->add('photo', FileType::class, array_merge([
                'mapped' => false,
                'constraints' => [new Image()],
                'required' => false,
                'constraints' => [
                    new Assert\File(
                        maxSize: '2M',
                        extensions: ['jpeg', 'jpg', 'png', 'webp'],
                        extensionsMessage: 'Formats autorisés pour les images : JPG, PNG, WEBP',
                    )
                ],
            ], $this->getFieldClasses('photo')))

            ->add('isDisplayed', null, array_merge([
                'label' => 'Visible sur le menu',
            ], $this->getFieldClasses('isDisplayed')))

            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $product = $event->getData();
                $form = $event->getForm();

                $category = $product?->getCategory();

                $form
                    ->add('orderInCategory', ChoiceType::class, array_merge([
                        'label' => 'Position dans la catégorie',
                        'choices' => $this->getOrderChoices($category),
                    ], $this->getFieldClasses('orderInCategory', 'order-select')))

                    ->add('category', EntityType::class, array_merge([
                        'label' => 'Catégorie',
                        'class' => MenuCategory::class,
                        'choice_label' => 'title',
                    ], $this->getFieldClasses('category', 'category-select')));
            })

            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) { //pre submit event to repopulate the choices with the pertinent opitions before submit               
                $submittedData = $event->getData();
                $form = $event->getForm();
                $categoryId = $submittedData['category'] ?? null;

                $category = $categoryId
                    ? $this->categoryRepo->find($categoryId)
                    : null;

                $form
                    ->add('orderInCategory', ChoiceType::class, array_merge([
                        'label' => 'Position dans la catégorie',
                        'choices' => $this->getOrderChoices($category), //on post submit we fill the form with possible correct values
                        'choice_value' => fn($choice) => (string)$choice, //we use this line so the value field in html matches
                    ], $this->getFieldClasses('orderInCategory', 'order-select')));
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MenuProduct::class,
        ]);
    }


    private function getFieldClasses(string $field, string $secondClass = ""): array
    {
        return [
            'row_attr' => [
                'class' => "admin-menu-form__container__edit__row $field" . "Field"
            ],
            'label_attr' => [
                'class' => "admin-menu-form__container__edit__row__label $field" . "Field"
            ],
            'attr' => [
                'class' => "admin-menu-form__container__edit__row__input  $secondClass $field" . "Field",
            ],
        ];
    }

    private function getOrderChoices(?MenuCategory $category): array
    {
        if (!$category) return [];

        $products = $this->repo->findBy(
            ['category' => $category],
            ['orderInCategory' => 'ASC']
        );

        $choices = [];
        $choices[] = 10; //alwys add 10 for emty target case

        foreach ($products as $product) {
            $choices[] = $product->getOrderInCategory() - 5;
        }

        $choices[] = 10000;

        return $choices;
    }
}
