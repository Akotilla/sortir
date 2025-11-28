<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FiltreAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if($options['villes'] ?? false){
            $builder
                ->add('nomVille', TextType::class, [
                    'label' => 'Le nom contient',
                    'required' => false,
                    'attr' => ['class' => 'form-control']
                ])
            ;
        }
        if($options['campus'] ?? false){
            $builder
                ->add('nomCampus', TextType::class, [
                    'label' => 'Le nom contient',
                    'required' => false,
                    'attr' => ['class' => 'form-control']
                ])
            ;
        }
        if($options['utilisateurs'] ?? false){
            $builder
                ->add('nomUtilisateur', TextType::class, [
                    'label' => 'Le nom contient',
                    'required' => false,
                    'attr' => ['class' => 'form-control']
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'campus' => false,
            'villes' => false,
            'utilisateurs' => false,
        ]);
    }
}
