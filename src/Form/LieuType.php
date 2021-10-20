<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Ville;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LieuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du lieu: ',
            ])
            ->add('rue', TextType::class, ['label'=>'Rue: '])
            ->add('latitude', TextType::class, ['label'=>'Latitude: '])
            ->add('longitude', TextType::class, ['label'=>'Longitude: '])
            ->add('ville', EntityType::class, ['label'=>'Villes: ','placeholder'=>'Choisir la ville ',
                'class' => Ville::class,
                'choice_label' => 'nom',
                'query_builder' => function(EntityRepository $repository) {

                }
            ]);
            /*->add('send',SubmitType::class, [
                'label' => 'Ajouter',
                'attr' => [
                    'class' => 'btn btn-primary w-1950'
                ]
            ])
        ;*/
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Lieu::class,
        ]);
    }
}
