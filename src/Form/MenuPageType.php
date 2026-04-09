<?php

namespace App\Form;

use App\Entity\MenuPage;
use App\Repository\MenuPageRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuPageType extends AbstractType
{
    private MenuPageRepository $pageRepo;

    public function __construct(MenuPageRepository $pageRepo)
    {
        $this->pageRepo = $pageRepo;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, array_merge([
                'label' => 'Nom de la page',
            ], $this->getFieldClasses('title')))

            ->add('titleIsDisplayed', null, array_merge([
                'label' => 'Titre visible',
            ], $this->getFieldClasses('titleIsDisplayed')))

            ->add('description', TextareaType::class, array_merge([
                'label' => 'Description de la catégorie',
                'required' => false,
            ], $this->getFieldClasses('description')))

            ->add('pageIsDisplayed', null, array_merge([
                'label' => 'Visible sur le menu',
            ], $this->getFieldClasses('isDisplayed')))

            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $page = $event->getData();
                $form = $event->getForm();

                $form
                    ->add('pageOrder', ChoiceType::class, array_merge([
                        'label' => 'Position sur le menu',
                        'choices' => $this->getOrderChoices(),
                    ], $this->getFieldClasses('pageOrder', 'order-select')));
            })

            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) { //pre submit event to repopulate the choices with the pertinent opitions before submit               

                $form = $event->getForm();
                $form
                    ->add('pageOrder', ChoiceType::class, array_merge([
                        'label' => 'Position sur le menu',
                        'choices' => $this->getOrderChoices(), //on post submit we fill the form with possible correct values
                        'choice_value' => fn($choice) => (string)$choice, //we use this line so the value field in html matches
                    ], $this->getFieldClasses('pageOrder', 'order-select')));
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MenuPage::class,
        ]);
    }


    private function getFieldClasses(string $field, string $secondClass = ""): array
    {
        return [
            'row_attr' => [
                'class' => "admin-form__container__edit__row $field" . "Field page-theme"
            ],
            'label_attr' => [
                'class' => "admin-form__container__edit__row__label $field" . "Field page-theme"
            ],
            'attr' => [
                'class' => "admin-form__container__edit__row__input  $secondClass $field" . "Field page-theme",
            ],
        ];
    }

    private function getOrderChoices(): array
    {
        $pages = $this->pageRepo->findBy(
            [],
            ['pageOrder' => 'ASC']
        );

        $choices = [];
        $choices[] = 10; //alwys add 10 for emty target case

        foreach ($pages as $page) {
            $choices[] = $page->getPageOrder() - 5;
        }

        $choices[] = 10000;
        return $choices;
    }
}
