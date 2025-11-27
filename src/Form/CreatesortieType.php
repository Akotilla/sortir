<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Utilisateur;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CreatesortieType extends AbstractType
{
    public function __construct(private UrlGeneratorInterface $urlGenerator) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de la sortie',
                'attr' => ['class' => 'form-control']
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'label' => 'Date et heure de début',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('dateLimiteInscription', DateTimeType::class, [
                'label' => 'Date limite d\'inscription',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('nbInscriptionMax', IntegerType::class, [
                'label' => 'Nombre maximum d\'inscriptions',
                'attr' => ['class' => 'form-control']
            ])
            ->add('dureeHeures', IntegerType::class, [
                'label' => 'Heures',
                'attr' => ['min' => 0, 'type' => 'number',],
                'mapped' => false,
            ])
            ->add('dureeMinutes', IntegerType::class, [
                'label' => 'Minutes',
                'attr' => ['min' => 0,
                    'max' => 59,
                    'type' => 'number',],
                'mapped' => false,
            ])
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                /** @var Sortie $sortie */
                $sortie = $event->getData();
                $form = $event->getForm();

                $heures = $form->get('dureeHeures')->getData() ?? 0;
                $minutes = $form->get('dureeMinutes')->getData() ?? 0;

                $sortie->setDuree($heures * 60 + $minutes);
            })
            ->add('infosSortie', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['class' => 'form-control', 'rows' => 5]
            ])
            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'choice_label' => 'nom',
                'label' => 'Lieu',
                'attr' => ['class' => 'form-control'],
                'choice_attr' => function(Lieu $lieu, $key, $value) {
                    return ['data-ville' => $lieu->getVille()->getId()];
                },
                'required' => true,
            ])
            ->add('save', SubmitType::class, ['label' => 'Enregistrer'])
            ->add('publish', SubmitType::class, ['label' => 'Publier']);
        if ($options['is_edit'] ?? false) {
            $builder->add('delete', SubmitType::class, [
                'label' => 'Supprimer',
                'attr' => ['class' => 'btn btn-danger w-100 mt-2',
                    'formaction' => $this->urlGenerator->generate('sortie_delete', [
                        'id' => $builder->getData()->getId(),
                    ]),
                    'formmethod' => 'post',],
            ]);
        }


    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
            'is_edit' => false,
        ]);
    }
}
