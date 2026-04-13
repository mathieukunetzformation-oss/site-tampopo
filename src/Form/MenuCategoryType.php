<?php

namespace App\Form;

use App\Entity\MenuCategory;
use App\Entity\MenuPage;
use App\Repository\MenuCategoryRepository;
use App\Repository\MenuPageRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuCategoryType extends AbstractType
{

    private MenuCategoryRepository $categoryRepo;
    private MenuPageRepository $pageRepo;

    public function __construct(MenuCategoryRepository $categoryRepo, MenuPageRepository $pageRepo)
    {
        $this->categoryRepo = $categoryRepo;
        $this->pageRepo = $pageRepo;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, array_merge([
                'label' => 'Nom du produit',
            ], $this->getFieldClasses('title')))

            ->add('description', TextareaType::class, array_merge([
                'label' => 'Description de la catégorie',
                'required' => false,
            ], $this->getFieldClasses('description')))

            ->add('categoryIsDisplayed', null, array_merge([
                'label' => 'Visible sur le menu',
            ], $this->getFieldClasses('isDisplayed')))

            ->add('displayAsGrid', null, array_merge([
                'label' => 'Affichage en grille',
            ], $this->getFieldClasses('displayAsGrid')))

            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $category = $event->getData();
                $form = $event->getForm();

                $page = $category?->getPage();

                $form
                    ->add('orderInPage', ChoiceType::class, array_merge([
                        'label' => 'Position sur la page',
                        'choices' => $this->getOrderChoices($page),
                    ], $this->getFieldClasses('orderInPage', 'order-select')))

                    ->add('page', EntityType::class, array_merge([
                        'label' => 'Page',
                        'class' => MenuPage::class,
                        'choice_label' => 'title',
                    ], $this->getFieldClasses('page', 'page-select')));
            })

            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) { //pre submit event to repopulate the choices with the pertinent opitions before submit               
                $submittedData = $event->getData();
                $form = $event->getForm();
                $pageId = $submittedData['page'] ?? null;

                $page = $pageId
                    ? $this->pageRepo->find($pageId)
                    : null;

                $form
                    ->add('orderInPage', ChoiceType::class, array_merge([
                        'label' => 'Position sur la page',
                        'choices' => $this->getOrderChoices($page), //on post submit we fill the form with possible correct values
                        'choice_value' => fn($choice) => (string)$choice, //we use this line so the value field in html matches
                    ], $this->getFieldClasses('orderInPage', 'order-select')));
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MenuCategory::class,
        ]);
    }


    private function getFieldClasses(string $field, string $secondClass = ""): array
    {
        return [
            'row_attr' => [
                'class' => "admin-menu-form__container__edit__row $field" . "Field category-theme"
            ],
            'label_attr' => [
                'class' => "admin-menu-form__container__edit__row__label $field" . "Field category-theme"
            ],
            'attr' => [
                'class' => "admin-menu-form__container__edit__row__input  $secondClass $field" . "Field category-theme",
            ],
        ];
    }

    private function getOrderChoices(?MenuPage $page): array
    {
        if (!$page) return [];

        $categories = $this->categoryRepo->findBy(
            ['page' => $page],
            ['orderInPage' => 'ASC']
        );

        $choices = [];
        $choices[] = 10; //alwys add 10 for emty target case

        foreach ($categories as $category) {
            $choices[] = $category->getOrderInPage() - 5;
        }

        $choices[] = 10000;

        return $choices;
    }
}
