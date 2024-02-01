<?php

namespace AcMarche\Avaloir\Form;

use AcMarche\Avaloir\Data\Localite;
use AcMarche\Avaloir\Entity\Avaloir;
use AcMarche\Avaloir\Repository\VillageRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AvaloirEditType extends AbstractType
{
    public function __construct(private VillageRepository $villageRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'rueId',
                HiddenType::class,
                array(
                    'required' => true,
                    'mapped' => false,
                )
            )
            ->add(
                'rue',
                TextType::class,
                [
                    'help' => 'Le nom de la rue a été trouvé suivant les coordonnées gps',
                    'attr' => ['readonly' => false]
                ]
            )
            ->add(
                'localite',
                ChoiceType::class,
                [
                    'required'=>false,
                    'choices' => $this->villageRepository->getForSearch()
                ]
            )
            ->add(
                'numero',
                TextType::class,
                [
                    'label' => 'Numéro de maison',
                    'required' => false,
                ]
            )
            ->add(
                'description',
                TextareaType::class,
                array(
                    'required' => false,
                    'attr' => array('rows' => 5),
                )
            )
            ->add(
                'date_rappel',
                DateType::class,
                array(
                    
                    'required' => false,
                    'label' => 'Date de rappel',
                    'attr' => array('autocomplete' => 'off'),
                )
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            array(
                'data_class' => Avaloir::class,
            )
        );
    }
}
