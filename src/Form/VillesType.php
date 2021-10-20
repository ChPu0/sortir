<?php

namespace App\Form;

use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VillesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class,['label'=>'Ville: '])
            ->add('codePostal', TextType::class, ['label'=>'Code postal: '])
           /**
        ->add('submit',SubmitType::class, [
            'label' => 'Ajouter',
            'attr' => [
                'class' => 'btn btn-primary w-100']
            ])
        */
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ville::class,
        ]);
    }
}
