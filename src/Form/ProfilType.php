<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Participant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo',TextType::class,[
                'label'=>'Pseudo : ',
                'required'=>'true'
            ])
            ->add('prenom',TextType::class,[
                'label'=>'Prénom : ',
                'required'=>'true'
            ])
            ->add('nom',TextType::class,[
                'label'=>'Nom : ',
                'required'=>'true'
            ])
            ->add('telephone',TextType::class,[
                'label'=>'Téléphone : ',
                'required' => false
            ])
            ->add('email',EmailType::class,[
                'label'=>'Email : ',
                'required'=>'true'
            ])
            //->add('roles')
            ->add('password',RepeatedType::class,[
                'type' => PasswordType::class,
                'mapped' => false,
                'first_options' => ['label'=>'Mot de passe : '],
                'second_options' => ['label'=>'Confirmation :'],
                'invalid_message' => 'Les mots de passe doivent être identiques',
                'required'=>'true'
            ])
            ->add('administrateur')
            ->add('actif')
            //->add('sorties')
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'label'=>'Campus : ',
                'choice_label' => 'nom',
                'placeholder' => '-- Choisir --',
            ])
            ->add('image', FileType::class, [
                'label'=> 'Photo Profil',
                'mapped'=>false,
                'required'=>false,
                'constraints' => [
                    new File([
                        'maxSize' => '3000k',
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
