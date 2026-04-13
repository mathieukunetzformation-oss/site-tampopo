<?php

namespace App\Form;

use App\Entity\ProductOffer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductOfferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('price', null, array_merge([
                'label' => "Prix"
            ], $this->getFieldClasses('price')))

            ->add('quantity', IntegerType::class, array_merge([
                'label' => "Quantité"
            ], $this->getFieldClasses('quantity')));
    }

    private function getFieldClasses(string $field, string $secondClass = ""): array
    {
        return [
            'row_attr' => [
                'class' => "admin-menu-form__container__edit__row__offerItem__row $field" . "Field"
            ],
            'label_attr' => [
                'class' => "admin-menu-form__container__edit__row__offerItem__row__label $field" . "Field"
            ],
            'attr' => [
                'class' => "admin-menu-form__container__edit__row__offerItem__row__input  $secondClass $field" . "Field",
            ],
        ];
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductOffer::class,
        ]);
    }
}
