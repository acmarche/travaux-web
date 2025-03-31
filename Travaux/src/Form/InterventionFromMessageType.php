<?php

namespace AcMarche\Travaux\Form;

use AcMarche\Travaux\Entity\Intervention;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InterventionFromMessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('intitule')
            ->add(
                'descriptif',
                TextareaType::class,
                array(
                    'required' => true,
                    'attr' => array('rows' => 8),
                )
            )
            ->add(
                'attachments',
                ChoiceType::class,
                array(
                    'required' => false,
                    'choices' => $options['attachments'],
                    'label' => 'Attachements',
                    'multiple' => true,
                    'expanded' => true,
                )
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Intervention::class,
            'attachments' => [],
        ]);
        $resolver->setAllowedTypes('attachments', 'array');
    }
}
