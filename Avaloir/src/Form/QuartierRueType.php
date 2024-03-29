<?php

namespace AcMarche\Avaloir\Form;

use AcMarche\Avaloir\Entity\Quartier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuartierRueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'rueIds',
                RueAutocompleteField::class,
                [
                    'required' => false,
                    'label' => 'Rues',
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            array(
                'data_class' => Quartier::class,
            )
        );
    }
}
