<?php

namespace App\Form;

use App\Entity\Campus;
use App\Repository\CampusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ListSortieType extends AbstractType
{


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //$listCampus  = $campusRepository->findAll();

        $builder

            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom',
                'query_builder' => function(EntityRepository $repository) {},
                'attr' => [
                    'id' => "campus",
                    'class' => 'form-select'
                ]
            ])

            ->add('nom', SearchType::class, [
                'label' => 'Le nom de la sortie contient : ',
                'required' => false,
                'trim' => true,
                'attr' => [
                    'name' => "nom",
                    'class' => 'form-control w-50'
                ]
            ])
            ->add('dateHeureMin', DateType::class, [
                'required' => false,
                'label' => 'Entre : ',
                'widget' => 'single_text',
                'attr' => [
                    'name' => 'dateHeureMin'
                ]
            ])
            ->add('dateHeureMax', DateType::class, [
                'required' => false,
                'label' => 'Et : ',
                'widget' => 'single_text',
                'attr' => [
                    'name' => 'dateHeureMax'
                ]
            ])

            ->add('organisateur', CheckboxType::class,[
                'required' => false,
                'label' => 'Sorties dont je suis l\'organisateur/trice',
                'attr' => [
                    'name' => "organisateur",
                    'class' => "form-check-input"
                ]
            ])

            ->add('isInscrit', CheckboxType::class,[
                'required' => false,
                'label' => 'Sorties auxquelles je suis inscrit/e',
                'attr' => [
                    'name' => "inscrit",
                    'class' => "form-check-input"
            ]])
             ->add('isNotInscrit', CheckboxType::class,[
                'required' => false,
                'label' => 'Sorties auxquelles je ne suis pas inscrit/e',
                'attr' => [
                    'name' => "non-inscrit",
                    'class' => "form-check-input"
            ]])


            ->add('etat', CheckboxType::class,[
                'required' => false,
                'label' => 'Sorties passées',
                'attr' => [
                    'name' => "Passée",
                    'class' => "form-check-input"
                ]
            ])

            ->add('rechercher', SubmitType::class, [
                'attr' => [
                    'type' => "submit",
                    'class' => "btn btn-outline-primary btn-lg"
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
