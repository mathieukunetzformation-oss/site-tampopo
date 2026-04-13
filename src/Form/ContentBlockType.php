<?php

namespace App\Form;

use App\Entity\ContentBlock;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;


class ContentBlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $blockType = $options['block_type']; //safer than just $blockType = $builder->getData()->getType();

        if ($blockType === 'photo') {
            $builder->add('photo', FileType::class, array_merge([
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
            ], $this->getFieldClasses('photo')));
        } else {
            $builder->add('textContent', TextareaType::class, array_merge([
                'required' => false,
                'label' => "Contenu texte",
            ], $this->getFieldClasses('text')));
        }
    }

    private function getFieldClasses(string $field): array
    {
        return [
            'row_attr' => [
                'class' => "adminContentBlockForm__form__row $field" . "Field"
            ],
            'label_attr' => [
                'class' => "adminContentBlockForm__form__row__label $field" . "Field"
            ],
            'attr' => [
                'class' => "adminContentBlockForm__form__row__input $field" . "Field",
            ],
        ];
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContentBlock::class,
            'block_type' => null,
        ]);
    }
}
