<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Lieu;

use App\Entity\Sortie;

use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class CreeSortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom',TextType::class, ['label'=>'Nom de la sortie: ', 'required'=>'true', ])
            ->add('dateHeureDebut',DateTimeType::class,[
                'label'    => 'Date début de la sortie: ', 'widget'=>'single_text'
                ])
            ->add('dateLimiteInscription',DateType::class,[
                'label'    => 'Date limite d\'inscription: ', 'widget'=>'single_text'])

            ->add('nbinscriptionsmax',IntegerType::class, [
                'label' => 'Nombre de places: '
            ])

            ->add('duree',null, ['label'=>'Durée: '])


            ->add('infosSortie',TextareaType::class, [
                'label' => 'Description et infos: '
            ])
            ->add('lieu',EntityType::class, ['placeholder'=>'Choisir le lieu', 'label'=>'Lieu: ',
                'class' => Lieu::class,
                'choice_label' => 'nom',
                'query_builder' => function(EntityRepository $repository) {

                }
            ])
            ->add('campus',EntityType::class, [
                'class' => Campus::class, 'placeholder'=>'Choisir le campus', 'choice_label'=>'nom', 'label'=>'Campus: ',
                'query_builder' => function(EntityRepository $repository) {

                }
            ])
            ->add('ville', EntityType::class,['mapped'=>false, 'class'=>Ville::class,'placeholder'=>'Choisir la ville ','choice_label'=>'nom'])

            ->add('rue',EntityType::class,['class'=>Lieu::class,'mapped'=>false,'label'=>'Rue: ', 'choice_label'=>'rue', 'placeholder'=>'Choisir la rue'])
            ->add('codePostale',IntegerType::class,['mapped'=>false, 'label'=>'Code postale: '] )
            ->add('latitude', IntegerType::class, ['mapped'=>false, 'label'=>'Latitude: '])
            ->add('longitude', IntegerType::class, ['mapped'=>false, 'label'=>'Longiture: '
            ]);
/**
            ->add('save', SubmitType::class,[
                'label' =>'Enregistrer',
                'attr' => [
                    'class' => 'btn btn-success w-50'
                ]
            ])

            ->add('publish', SubmitType::class,[
                'label' =>'Publier',
                'attr' => [
                    'class' => 'btn btn-primary w-50'
                ]
            ])
            ->add('cancel', SubmitType::class,[
                'label' =>'Annuler',
                'attr' => [
                    'class' => 'btn btn-warning w-50']
            ]);

*/
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}