<?php

namespace AcMarche\Travaux\Form;

use AcMarche\Travaux\Entity\Suivi;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SuiviType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'descriptif',
                TextareaType::class,
                array(
                    'required' => true,
                    'attr' => array('rows' => 5),
                )
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            array(
                'data_class' => Suivi::class,
            )
        );
    }
}
