<?php

namespace AcMarche\Travaux\Form;

use AcMarche\Travaux\Entity\CategoryPlanning;
use AcMarche\Travaux\Entity\Employe;
use AcMarche\Travaux\Repository\CategoryPlanningRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmployeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('prenom')
            ->add('categories', EntityType::class, [
                'class' => CategoryPlanning::class,
                'query_builder' => fn(CategoryPlanningRepository $categoryPlanningRepository
                ) => $categoryPlanningRepository->getQblForList(),
                'label' => 'Equipe(s)',
                'required' => true,
                'multiple' => true,
                'expanded' => true,
                'placeholder' => 'Choisissez une équipe',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'data_class' => Employe::class,
        ));
    }
}
