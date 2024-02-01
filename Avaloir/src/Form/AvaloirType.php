<?php

namespace AcMarche\Avaloir\Form;

use AcMarche\Avaloir\Entity\Avaloir;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AvaloirType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'dates',
                CollectionType::class,
                array(
                    'entry_type' => DateNettoyageType::class,
                    'required' => false,
                    'allow_add' => false,
                    'label' => ' ',
                    'prototype' => true,
                    'allow_delete' => false,
                )
            )
            ->add('rue', TextType::class, [
                'required' => true,
                'mapped' => false,
                'attr' => [
                    'data-autocomplete-target' => 'input',
                ],
            ])
            ->add(
                'numero',
                TextType::class,
                [
                    'required' => false,
                    'help' => 'Emplacement approximatif dans la rue',
                ]
            )
            ->add(
                'descriptif',
                TextareaType::class,
                array(
                    'required' => false,
                    'attr' => array('rows' => 5),
                )
            )
            ->add(
                'date_rappel',
                DateType::class,
                array(
                    
                    'required' => false,
                    'label' => 'Date de rappel',
                    'attr' => array('autocomplete' => 'off'),
                )
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            array(
                'data_class' => Avaloir::class,
            )
        );
    }
}
