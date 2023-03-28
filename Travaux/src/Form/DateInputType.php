<?php

namespace AcMarche\Travaux\Form;

use AcMarche\Travaux\Entity\DateEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateInputType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('day', DateType::class, [
            'label' => 'Date',
            'widget' => 'single_text',
            'required' => true,
            'attr' => array('autocomplete' => 'off'),
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DateEntity::class,
        ]);
    }

}