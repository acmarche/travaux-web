<?php

namespace AcMarche\Stock\Form;

use AcMarche\Stock\Repository\CategorieRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class SearchProduitType extends AbstractType
{
    public function __construct(private CategorieRepository $categorieRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $categories = $this->categorieRepository->getForSearch();

        $builder
            ->add(
                'categorie',
                ChoiceType::class,
                array(
                    'choices' => $categories,
                    'required' => false,
                    'placeholder' => 'Choisissez une catégorie',
                )
            )
            ->add(
                'nom',
                SearchType::class,
                array(
                    'required' => false,
                    'attr' => array(
                        'placeholder' => 'Nom',
                    ),
                )
            )->add(
                'raz',
                SubmitType::class,
                [
                    'attr' => ['class' => ' mr-1 btn-primary ', 'title' => 'Réinitialiser la recherche'],
                ]
            );
    }

}
