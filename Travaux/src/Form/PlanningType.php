<?php

namespace AcMarche\Travaux\Form;

use AcMarche\Travaux\Entity\CategoryPlanning;
use AcMarche\Travaux\Entity\Horaire;
use AcMarche\Travaux\Entity\InterventionPlanning;
use AcMarche\Travaux\Repository\CategoryPlanningRepository;
use AcMarche\Travaux\Repository\HoraireRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;

class PlanningType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => true,
                'attr' => ['rows' => 3],
            ])
            ->add('horaire', EntityType::class, [
                'required' => true,
                'placeholder' => 'Choisissez un horaire',
                'class' => Horaire::class,
                'expanded' => true,
                'query_builder' => fn(HoraireRepository $horaireRepository) => $horaireRepository->getQblForList(),
            ])
            ->add('lieu', TextType::class, [
                'label' => 'Lieu',
                'required' => true,
            ])
            ->add('category', EntityType::class, [
                'label' => 'Equipe',
                'required' => true,
                'placeholder' => 'Choisissez une catégorie',
                'query_builder' => fn(CategoryPlanningRepository $domaineRepository
                ) => $domaineRepository->getQblForList(),
                'class' => CategoryPlanning::class,
            ])
            ->add('datesCollection', CollectionType::class, [
                'entry_type' => DateType::class,
                'label' => 'Dates',
                'required' => true,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'attr' => [
                    'autocomplete' => 'off',
                    'data-entry-add-label' => 'Ajouter une date',
                    'data-entry-remove-label' => 'Supprimer une date',
                ],
                'constraints' => [
                    new Count(min: 1, minMessage: 'Il doit y avoir au moins 1 date'),
                ],
            ])
            ->add('employes', EmployeAutocompleteField::class, [
                'extra_options' => [
                    'dateIntervention' => $options['dateSelected']?->format('Y-m-d'),
                    'categoryId' => $builder->getData()->category?->getId() ?? 0,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'data_class' => InterventionPlanning::class,
            'dateSelected' => null,
        ));
        $resolver->setAllowedTypes('dateSelected', ['null','DateTimeInterface']);
    }
}
