<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name',TextType::class,[
            'attr'=>['class' => 'form-control','']
        ])
            ->add('phone',TextType::class,[
                'attr'=>['class' => 'form-control','']
            ])
            ->add('username',TextType::class,[
                'attr'=>['class' => 'form-control','']
            ])
            ->add('email',TextType::class,[
                'attr'=>['class' => 'form-control','']
            ])

            ->add('password',PasswordType::class,[
                'attr'=>['class' => 'form-control','']
            ])
            ->add('roles', ChoiceType::class, [
                'data'=>"ROLE_USER",
                'choices' => ['ROLE_USER'=>'ROLE_USER','ROLE_ADMIN'=>'ROLE_ADMIN'],
                'multiple' => true,
                'expanded' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
