<?php

namespace AcMarche\Travaux\Form;

use AcMarche\Travaux\Entity\Batiment;
use AcMarche\Travaux\Entity\Domaine;
use AcMarche\Travaux\Entity\Etat;
use AcMarche\Travaux\Entity\Intervention;
use AcMarche\Travaux\Entity\Priorite;
use AcMarche\Travaux\Entity\Service;
use AcMarche\Travaux\Repository\CategorieRepository;
use AcMarche\Travaux\Repository\EtatRepository;
use AcMarche\Travaux\Repository\PrioriteRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class InterventionType extends AbstractType
{
    public function __construct(
        private readonly EtatRepository $etatRepository,
        private readonly PrioriteRepository $prioriteRepository,
        private readonly CategorieRepository $categorieRepository,
        private readonly AuthorizationCheckerInterface $authorizationChecker
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $admin = $this->authorizationChecker->isGranted('ROLE_TRAVAUX_ADMIN');
        $redacteur = $this->authorizationChecker->isGranted('ROLE_TRAVAUX_REDACTEUR');
        $editeur = $this->authorizationChecker->isGranted('ROLE_TRAVAUX_EDITEUR');

        if ($admin || $redacteur || $editeur) {
            $etats = $this->etatRepository->findAllForList();
        } else {
            $etats = $this->etatRepository->onlyNewForList();
        }

        $priorities = $this->prioriteRepository->getForList();
        $categories = $this->categorieRepository->getForList();

        if (!$admin) {
            $categories = $this->categorieRepository->getInterventionForList();
        }

        $builder
            ->add('intitule')
            ->add(
                'domaine',
                EntityType::class,
                array(
                    'class' => Domaine::class,
                    'label' => 'Type',
                    'required' => false,
                    'attr' => ['class' => 'custom-select my-1 mr-sm-2'],
                )
            )
            ->add(
                'service',
                EntityType::class,
                [
                    'class' => Service::class,
                    'required' => false,
                    'attr' => ['class' => 'custom-select my-1 mr-sm-2'],
                ]
            )
            ->add(
                'batiment',
                EntityType::class,
                [
                    'class' => Batiment::class,
                    'required' => false,
                    'attr' => ['class' => 'custom-select my-1 mr-sm-2'],
                ]
            )
            ->add(
                'transmis',
                CheckboxType::class,
                array(
                    'required' => false,
                    'label' => 'Transmis ?',
                    'attr' => array(),
                )
            )
            ->add(
                'affectePrive',
                CheckboxType::class,
                array(
                    'required' => false,
                    'label' => 'Affecté à un privé',
                )
            )
            ->add(
                'need_visit',
                CheckboxType::class,
                array(
                    'required' => false,
                    'label' => 'Besoin d\'une visite',
                    'help' => 'Une visite sur place est nécessaire',
                )
            )
            ->add(
                'date_rappel',
                DateType::class,
                array(

                    'label' => 'Date de rappel',
                    'required' => false,
                    'attr' => array('autocomplete' => 'off'),
                )
            )
            ->add(
                'descriptif',
                TextareaType::class,
                array(
                    'required' => true,
                    'attr' => array('rows' => 5),
                )
            )
            ->add('affectation')
            ->add(
                'soumis_le',
                DateType::class,
                array(

                    'label' => 'Soumis le',
                    'required' => false,
                    'attr' => array('autocomplete' => 'off'),
                )
            )
            ->add(
                'solution',
                TextareaType::class,
                array(
                    'required' => false,
                    'attr' => array('rows' => 5),
                )
            )
            ->add(
                'date_solution',
                DateType::class,
                array(

                    'label' => 'Date de solution',
                    'required' => false,
                    'attr' => array('autocomplete' => 'off'),
                )
            )
            ->add(
                'cout_main',
                MoneyType::class,
                array(
                    'required' => false,
                    'label' => 'Coût main d\'oeuvre',
                    'help' => 'Uniquement les chiffres',
                )
            )
            ->add(
                'cout_materiel',
                MoneyType::class,
                array(
                    'required' => false,
                    'label' => 'Coût matériel',
                    'help' => 'Uniquement les chiffres',
                )
            )
            ->add(
                'date_execution',
                DateType::class,
                array(

                    'label' => 'A réaliser à partir du',
                    'required' => false,
                    'attr' => array('autocomplete' => 'off'),
                )
            )
            ->add(
                'etat',
                EntityType::class,
                array(
                    'class' => Etat::class,
                    'required' => true,
                    'choices' => $etats,
                    'attr' => ['class' => 'custom-select my-1 mr-sm-2'],
                )
            )->add(
                'priorite',
                EntityType::class,
                array(
                    'class' => Priorite::class,
                    'required' => true,
                    'choices' => $priorities,
                    'attr' => ['class' => 'custom-select my-1 mr-sm-2'],
                )
            )
            ->add(
                'etat',
                EntityType::class,
                array(
                    'class' => Etat::class,
                    'required' => true,
                    'multiple' => false,
                    'choices' => $etats,
                    'attr' => ['class' => 'custom-select my-1 mr-sm-2'],

                )
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            array(
                'data_class' => Intervention::class,
            )
        );
    }
}
