<?php

namespace App\Form;

use App\Entity\Lieu;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class FilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'choice_label' => 'nom',
                'query_builder' => function (EntityRepository $repository) {
                    return $repository->createQueryBuilder('c')->orderBy('c.nom', 'ASC');
                },
                'required' => false,
                'empty_data' => null,
                'mapped' => false
            ])
            ->add('start', TextType::class, [
                'label' => 'Date Evenement (début)',
                'attr' => [
                    'class' => 'form-control datetimepicker-input',
                    'data-toggle' => 'datetimepicker',
                    'data-target' => '#filter_start'
                ],
                'required' => false,
                'empty_data' => null,
                'mapped' => false
            ])
            ->add('close', TextType::class, [
                'label' => 'Date Evenement (fin)',
                'attr' => [
                    'class' => 'form-control datetimepicker-input',
                    'data-toggle' => 'datetimepicker',
                    'data-target' => '#filter_close'
                ],
                'required' => false,
                'empty_data' => null,
                'mapped' => false
            ])
            ->add('ownorganisateur', CheckboxType::class, [
                'label' => 'Sorties dont je suis organisateur',
                'required' => false,
                'empty_data' => null,
                'mapped' => false
            ])
            ->add('subscibed', CheckboxType::class, [
                'label' => 'Sorties auxquelle je suis inscrit',
                'required' => false,
                'empty_data' => null,
                'mapped' => false
            ])
            ->add('unsubscribed', CheckboxType::class, [
                'label' => 'Sorties auxquelles je ne suis pas inscrit',
                'required' => false,
                'empty_data' => null,
                'mapped' => false
            ])
            ->add('passed', CheckboxType::class, [
                'label' => 'Sorties passées',
                'required' => false,
                'empty_data' => null,
                'mapped' => false
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Filtrer',
                'attr' => [
                    'class' => 'btn btn-success w-100'
                ]
            ]);
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'validation_groups' => false,
        ]);
    }

}