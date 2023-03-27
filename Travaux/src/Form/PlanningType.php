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
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlanningType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateIntroduction', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Date de rappel',
                'required' => false,
                'attr' => array('autocomplete' => 'off'),
            ])
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
            ->add('employes', EmployeAutocompleteField::class,[

            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'data_class' => Intervention::class,
        ));
    }
}
