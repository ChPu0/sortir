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
                'required' => true,
                'label'=>'Nom'])
            ->add('rue', TextType::class, [
                'required' => true,
                'label'=>'Rue'])
            ->add('latitude', TextType::class, [
                'required' => false,
                'label'=>'Latitude'])
            ->add('longitude', TextType::class, [
                'required' => false,
                'label'=> 'Longitude'])
            ->add('ville', EntityType::class, [
                'class'=>Ville::class,
                'placeholder' => '-- Choisir --',
                'choice_label'=>'nom'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void

    {
        $resolver->setDefaults([
            'data_class' => Lieu::class,
        ]);
    }
}
