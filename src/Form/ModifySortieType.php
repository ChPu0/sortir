<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModifySortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, ['label' => 'Nom de la sortie: ', 'required' => 'true',])
            ->add('dateHeureDebut', DateTimeType::class, [
                'label' => 'Date début de la sortie: ', 'widget' => 'single_text'
            ])
            ->add('dateLimiteInscription', DateType::class, [
                'label' => 'Date limite d\'inscription: ', 'widget' => 'single_text'])
            ->add('nbinscriptionsmax', IntegerType::class, [
                'label' => 'Nombre d\'inscription maximum: ',
                'required'=>false
            ])
            ->add('duree', null, ['label' => 'Durée: '])
            ->add('infosSortie', TextareaType::class, [
                'label' => 'Description et infos: ',
                'required'=>false
            ])
            ->add('lieu', EntityType::class, ['placeholder' => 'Choisir le lieu',
                'class' => Lieu::class,
                'choice_label' => 'nom',
                'query_builder' => function (EntityRepository $repository) {

                }
            ])
            ->add('campus', EntityType::class, [
                'class' => Campus::class, 'placeholder' => 'Choisir le campus', 'choice_label' => 'nom',
                'query_builder' => function (EntityRepository $repository) {

                }
            ])
            ->add('latitude', IntegerType::class, ['mapped' => false, 'label' => 'Latitude: ', 'required' => false ])
            ->add('longitude', IntegerType::class, ['mapped' => false, 'label' => 'Longiture: ', 'required' => false
            ])

            //Ajout des boutons de type SubmitTye + bootstrap
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => [
                    'class' => 'btn btn-primary w-100'
                ]
            ])
            ->add('publish', SubmitType::class, [
                'label' => 'Publier la sortie',
                'attr' => [
                    'class' => 'btn btn-success w-100'
                ]
            ])
            ->add('Annuler', SubmitType::class, [
                'label' => 'Retour',
                'attr' => [
                    'class' => 'btn btn-dark w-100']
            ])
            ->add('delete', SubmitType::class, [
                'label' => 'Annuler la sortie',
                'attr' => [
                    'class' => 'btn btn-danger w-100']
            ]);


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class
        ]);

    }
}