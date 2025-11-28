<?php

namespace App\Form;

use App\Entity\Campus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CampusAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du campus',
                'required' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Créer',
                'attr' => ['class' => 'btn btn-success mt-2'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'=>Campus::class,
        ]);
    }
}
