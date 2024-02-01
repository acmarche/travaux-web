<?php

namespace AcMarche\Avaloir\Form\Search;

use AcMarche\Avaloir\Repository\RueRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;

class SearchAvaloirType extends AbstractType
{
    public function __construct(private RueRepository $rueRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $villages = $this->rueRepository->getVillages();

        $builder
            ->add(
                'rue',
                SearchType::class,
                array(
                    'required' => false,
                    'attr' => ['placeholder' => 'Nom de rue'],
                )
            )
            ->add(
                'village',
                ChoiceType::class,
                array(
                    'choices' => $villages,
                    'required' => false,
                    'placeholder' => 'Choisissez un village',
                )
            )
            ->add(
                'id',
                IntegerType::class,
                array(
                    'required' => false,
                    'attr' => array(
                        'placeholder' => 'Identifiant',
                    ),
                )
            )
            ->add(
                'date_debut',
                DateType::class,
                array(
                    
                    'label' => 'Date de début',
                    'required' => false,
                    'attr' => array(
                        'placeholder' => 'Entre le',
                    ),
                )
            )
            ->add(
                'date_fin',
                DateType::class,
                array(
                    
                    'label' => 'Date de fin',
                    'required' => false,
                    'attr' => array(
                        'placeholder' => 'Et le',
                    ),
                )
            );
    }
}
