<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\DBAL\Types\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;

class SelectSortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $sortie = new Sortie();

        $builder
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom'
            ])
            ->add('nom', SearchType::class, [
                'label' => 'Le nom de la sortie contient : '
            ])
            ->add('dateHeureDebut', RepeatedType::class, [
                'type' => DateType::class,
                        'first_options' => ['label' => 'Entre : ', 'widget' => 'single_text'],
                        'second_options' => ['label' => 'Et : ', 'widget' => 'single_text']
            ])
            //->add('dateHeureDebut')
            //->add('duree')
            //->add('dateLimiteInscription')
            //->add('nbInscriptionsMax')
            //->add('infosSortie')
            //->add('lieu')
            /*->add('etat', EntityType::class, [
                'class' => Etat::class,
                'choice_label' => 'libelle'
            ])*/
            ->add('organisateur', CheckboxType::class,[
                'label' => 'Sorties dont je suis l\'organisateur/trice'
            ])

            ->add('inscrits', CheckboxType::class,[
                'label' => 'Sorties auxquelles je suis inscrit/e'
            ])

            /*->add('inscrits', EntityType::class, [
                'class' => Sortie::class,

                'choice_label' => 'inscrits',

                'choice_attr' => function ($inscrits, $key, $index) use ($sortie) {
                    $selected = false;
                    if(in_array($sortie->getInscrits(), $inscrits)) {
                        $selected = true;
                    }
                    return ['checked' => $selected];
                },

                'multiple' => true,
                'expanded' => true,
                'data' => null
            ])*/

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class
        ]);
    }
}
