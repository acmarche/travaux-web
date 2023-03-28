<?php

namespace AcMarche\Travaux\Form;

use AcMarche\Travaux\Entity\Batiment;
use AcMarche\Travaux\Entity\Domaine;
use AcMarche\Travaux\Entity\Horaire;
use AcMarche\Travaux\Entity\Intervention;
use AcMarche\Travaux\Repository\BatimentRepository;
use AcMarche\Travaux\Repository\DomaineRepository;
use AcMarche\Travaux\Repository\HoraireRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
            ->add('intitule', TextType::class, [

            ])
            ->add('descriptif', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['rows' => 8],
            ])
            ->add('horaire', EntityType::class, [
                'required' => true,
                'placeholder' => 'Choisissez un horaire',
                'class' => Horaire::class,
                'query_builder' => fn(HoraireRepository $horaireRepository) => $horaireRepository->getQblForList(),
            ])
            ->add('batiment', EntityType::class, [
                'label' => 'Lieu',
                'required' => true,
                'placeholder' => 'Choisissez un lieu',
                'class' => Batiment::class,
                'query_builder' => fn(BatimentRepository $batimentRepository) => $batimentRepository->getQblForList(),
            ])
            ->add('domaine', EntityType::class, [
                'label' => 'Equipe',
                'required' => true,
                'placeholder' => 'Choisissez une équipe',
                'query_builder' => fn(DomaineRepository $domaineRepository) => $domaineRepository->getQblForList(),
                'class' => Domaine::class,
            ])
            ->add('employes', EmployeAutocompleteField::class, [

            ])
            ->add('datesCollection', CollectionType::class, [
                'entry_type' => DateType::class,
                'required' => true,
                'entry_options' => ['label' => false, 'widget' => 'single_text'],
                'allow_add' => true,
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
            ->add('save', SubmitType::class, [
                'label' => 'Sauvegarder',
                'attr' => ['class' => 'btn-success'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'data_class' => Intervention::class,
        ));
    }
}
