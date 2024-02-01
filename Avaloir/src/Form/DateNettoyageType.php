<?php

namespace AcMarche\Avaloir\Form;

use AcMarche\Avaloir\Entity\DateNettoyage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateNettoyageType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'jour',
                DateType::class,
                array(
                    
                    'label' => 'Date de nettoyage',
                    'required' => true,
                    'attr' => array( 'autocomplete' => 'off'),
                )
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            array(
                'data_class' => DateNettoyage::class,
            )
        );
    }
}
