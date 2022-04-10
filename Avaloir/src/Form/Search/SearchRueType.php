<?php

namespace AcMarche\Avaloir\Form\Search;

use AcMarche\Avaloir\Repository\RueRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;

class SearchRueType extends AbstractType
{
    public function __construct(private RueRepository $rueRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $villages = $this->rueRepository->getVillages();
        $builder
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
                'nom',
                SearchType::class,
                array(
                    'required' => false,
                    'attr' => array(
                        'placeholder' => 'Nom de la rue',
                    ),
                )
            );
    }

}
