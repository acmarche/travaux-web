<?php

namespace AcMarche\Travaux\Form;

use AcMarche\Travaux\Entity\Absence;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AbsenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('raison', ChoiceType::class, [
                'label' => 'Raison',
                'placeholder' => '',
                'required' => true,
                'choices' => ['Congé' => 'congé', 'Maladie' => 'maladie'],
            ])
            ->add(
                'date_begin',
                DateType::class,
                [
                    'label' => 'Date de début',
                    'help' => 'Date y compris',
                    'required' => true,
                    'widget' => 'single_text',
                ]
            )
            ->add(
                'date_end',
                DateType::class,
                [
                    'label' => 'Date de fin',
                    'help' => 'Date y compris',
                    'required' => true,
                    'widget' => 'single_text',
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'data_class' => Absence::class,
        ));
    }
}
