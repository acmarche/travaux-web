<?php

namespace AcMarche\Travaux\Form\Search;

use AcMarche\Travaux\Repository\BatimentRepository;
use AcMarche\Travaux\Repository\CategorieRepository;
use AcMarche\Travaux\Repository\DomaineRepository;
use AcMarche\Travaux\Repository\EtatRepository;
use AcMarche\Travaux\Repository\PrioriteRepository;
use AcMarche\Travaux\Repository\UserRepository;
use AcMarche\Travaux\Service\WorkflowEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class SearchInterventionType extends AbstractType
{
    public function __construct(
        private readonly BatimentRepository $batimentRepository,
        private readonly CategorieRepository $categorieRepository,
        private readonly UserRepository $userRepository,
        private readonly DomaineRepository $domaineRepository,
        private readonly EtatRepository $etatRepository,
        private readonly PrioriteRepository $prioriteRepository,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $batiments = $this->batimentRepository->getForSearch();
        $users = $this->userRepository->getForSearch();
        $categories = $this->categorieRepository->getForSearch();
        $domaines = $this->domaineRepository->getForSearch();
        $etats = $this->etatRepository->getForSearch();
        $priorites = $this->prioriteRepository->getForSearch();
        $affecte_prive = ['Oui' => 1, 'Non' => 0];
        $archive = ['Les deux' => null, 'Oui' => 1, 'Non' => 0];

        $sorts = array(
            'Numéro' => 'id',
            'Intitule' => 'intitule',
            'Priorité' => 'priorite',
            'Date' => 'date_introduction',
        );

        $builder
            ->add(
                'intitule',
                SearchType::class,
                array(
                    'required' => false,
                    'attr' => array(
                        'placeholder' => 'Mot clef',
                    ),
                )
            )
            ->add(
                'id',
                IntegerType::class,
                array(
                    'required' => false,
                    'attr' => array(
                        'placeholder' => 'Numéro',
                    ),
                )
            )
            ->add(
                'etat',
                ChoiceType::class,
                array(
                    'choices' => $etats,
                    'required' => false,
                    'placeholder' => 'Choisissez un état',
                    'attr' => ['class' => 'custom-select my-1 mr-sm-2'],
                )
            )
            ->add(
                'date_debut',
                DateType::class,
                array(

                    'label' => 'Date d\'introduction',
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

                    'label' => 'Date d\'introduction',
                    'required' => false,
                    'attr' => array(
                        'placeholder' => 'Et le',
                        'class' => 'datepicker',
                    ),
                )
            )
            ->add(
                'priorite',
                ChoiceType::class,
                array(
                    'choices' => $priorites,
                    'required' => false,
                    'placeholder' => 'Choisissez une priorité',
                    'attr' => ['class' => 'custom-select my-1 mr-sm-2'],
                )
            )
            ->add(
                'affecte_prive',
                ChoiceType::class,
                array(
                    'choices' => $affecte_prive,
                    'required' => false,
                    'placeholder' => 'Attribué à un privé',
                )
            )
            ->add(
                'batiment',
                ChoiceType::class,
                array(
                    'choices' => $batiments,
                    'required' => false,
                    'placeholder' => 'Choisissez un bâtiment',
                    'attr' => ['class' => 'custom-select my-1 mr-sm-2'],
                )
            )
            ->add(
                'user',
                ChoiceType::class,
                array(
                    'label' => 'Utilisateur',
                    'choices' => $users,
                    'required' => false,
                    'placeholder' => 'Choisissez un utilisateur',
                    'attr' => ['class' => 'custom-select my-1 mr-sm-2'],
                )
            )
            ->add(
                'categorie',
                ChoiceType::class,
                array(
                    'choices' => $categories,
                    'required' => false,
                    'placeholder' => 'Choisissez une catégorie',
                    'attr' => ['class' => 'custom-select my-1 mr-sm-2'],
                )
            )
            ->add(
                'domaine',
                ChoiceType::class,
                array(
                    'choices' => $domaines,
                    'required' => false,
                    'placeholder' => 'Choisissez un type',
                    'attr' => ['class' => 'custom-select my-1 mr-sm-2'],
                )
            )
            ->add(
                'placeSelected',
                EnumType::class,
                array(
                    'label' => 'Workflow',
                    'class' => WorkflowEnum::class,
                    'required' => false,
                    'placeholder' => 'Choisissez un type',
                    'choice_label' => fn(WorkflowEnum $place) => $place->getLabel(),
                    'attr' => ['class' => 'custom-select my-1 mr-sm-2'],
                )
            )
            ->add(
                'archive',
                ChoiceType::class,
                array(
                    'label' => 'Archive',
                    'choices' => $archive,
                    'required' => false,
                )
            )
            ->add(
                'sort',
                ChoiceType::class,
                array(
                    'choices' => $sorts,
                    'required' => false,
                    'placeholder' => 'Trier par',
                    'attr' => ['class' => 'custom-select my-1 mr-sm-2'],
                )
            )
            ->add(
                'raz',
                SubmitType::class,
                [
                    'attr' => ['class' => ' mr-1 btn-primary ', 'title' => 'Réinitialiser la recherche'],
                ]
            );
    }

}
