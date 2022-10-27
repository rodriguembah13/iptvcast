<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PrivilegeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('viewConfrontationdirect',CheckboxType::class,[
                'row_attr' => ['class' => 'form-check form-switch mb-2', 'id' => ''],
                'required'=>false,
                'attr'=>['class' => 'form-check-input','']
            ])
            ->add('viewScoreexact',CheckboxType::class,[
                'row_attr' => ['class' => 'form-check form-switch mb-2', 'id' => ''],
                'required'=>false,
                'attr'=>['class' => 'form-check-input','']
            ])
            ->add('viewInvincibilite',CheckboxType::class,[
                'row_attr' => ['class' => 'form-check form-switch mb-2', 'id' => ''],
                'required'=>false,
                'attr'=>['class' => 'form-check-input','']
            ])
            ->add('viewComportement',CheckboxType::class,[
                'row_attr' => ['class' => 'form-check form-switch mb-2', 'id' => ''],
                'required'=>false,
                'attr'=>['class' => 'form-check-input','']
            ])
            ->add('viewdeuxmarque',CheckboxType::class,[
                'row_attr' => ['class' => 'form-check form-switch mb-2', 'id' => ''],
                'required'=>false,
                'attr'=>['class' => 'form-check-input','']
            ])
            ->add('viewInvincibitemitemps',CheckboxType::class,[
                'row_attr' => ['class' => 'form-check form-switch mb-2', 'id' => ''],
                'required'=>false,
                'attr'=>['class' => 'form-check-input','']
            ])
            ->add('viewEventfulltime',CheckboxType::class,[
                'row_attr' => ['class' => 'form-check form-switch mb-2', 'id' => ''],
                'required'=>false,
                'attr'=>['class' => 'form-check-input','']
            ])
            ->add('viewEventhalftime',CheckboxType::class,[
                'row_attr' => ['class' => 'form-check form-switch mb-2', 'id' => ''],
                'required'=>false,
                'attr'=>['class' => 'form-check-input','']
            ])
            ->add('viewTrifulltime',CheckboxType::class,[
                'row_attr' => ['class' => 'form-check form-switch mb-2', 'id' => ''],
                'required'=>false,
                'attr'=>['class' => 'form-check-input','']
            ])
            ->add('viewTrihalftime',CheckboxType::class,[
                'row_attr' => ['class' => 'form-check form-switch mb-2', 'id' => ''],
                'required'=>false,
                'attr'=>['class' => 'form-check-input','']
            ])
            ->add('maxpronostic',TextType::class,[
                'required'=>false,
                'label'=>"Max pronostic",
                'attr'=>['class' => 'form-control','']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
           // 'data_class' => PrivilegeUser::class,
        ]);
    }
}
