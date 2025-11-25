<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Sortie;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('prenom')
            ->add('telephone')
            ->add('email')
            ->add('password', PasswordType::class, [
                'mapped' => false, // Ne pas mapper ce champ à l'entité
                'required' => false,
                'empty_data' => '',
                'label' => 'Mot de passe',
            ])
            ->add('password_confirm', PasswordType::class, [
                'mapped' => false,
                'required' => false,
                'empty_data' => '',
                'label' => 'Confirmer le nouveau mot de passe',
            ])
            ->add('pseudo')
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom',
                'disabled'=> true,
            ])
            ->add('image', FileType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Ma photo',
                'constraints' => [
                    new Image([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Merci de choisir une image valide',

                    ])
                ]
            ]);
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $wish = $event->getData();
            if ($wish && $wish->getFilename()) {
                // cas où on est en modification et qu'une image est déjà présente,
                // on ajoute un checkbox pour permettre de demander
                // la suppression de l'image
                $form = $event->getForm();
                $form->add('deleteImage', CheckboxType::class, [
                    'required' => false,
                    'mapped' => false,
                ]);
            }
        });
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $password = $form->get('password')->getData();
            $passwordConfirm = $form->get('password_confirm')->getData();

            if ($password !== $passwordConfirm && $password !== null) {
                $form->get('password_confirm')->addError(new \Symfony\Component\Form\FormError('Les mots de passe ne correspondent pas.'));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
