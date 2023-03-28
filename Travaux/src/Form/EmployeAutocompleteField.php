<?php

namespace AcMarche\Travaux\Form;

use AcMarche\Travaux\Entity\Employe;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\ParentEntityAutocompleteType;

#[AsEntityAutocompleteField]
class EmployeAutocompleteField extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class' => Employe::class,
            'label' => 'Ouvriers affectés',
            'choice_label' => 'nomPrenom',
            'multiple' => true,
            'constraints' => [
                new Count(min: 1, minMessage: 'Il doit y avoir au moins 1 ouvrier'),
            ],
        ]);
    }

    public function getParent(): string
    {
        return ParentEntityAutocompleteType::class;
    }
}