<?php

namespace AcMarche\Travaux\Form;

use AcMarche\Travaux\Entity\Employe;
use AcMarche\Travaux\Repository\EmployeRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField]
class EmployeAutocompleteField extends AbstractType
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class' => Employe::class,
            'label' => 'Ouvriers affectés',
            'choice_label' => 'nomPrenom',
            'multiple' => true,
            'autocomplete_url' => $this->urlGenerator->generate('planning_auto_complete'),
            'constraints' => [
                new Count(min: 1, minMessage: 'Il doit y avoir au moins 1 ouvrier'),
            ],
           /* 'query_builder' => function (Options $options) {
                return function (EmployeRepository $er) use ($options) {
                    $qb = $er->createQueryBuilder('employe');
                    $qb->andWhere('empolye');

                    $taxonsToBeExcluded = $options['extra_options']['dateIntervention'] ?? [];
                    if ([] !== $taxonsToBeExcluded) {
                        $qb->andWhere($qb->expr()->notIn('o.id', $taxonsToBeExcluded));
                    }

                    return $qb;
                };
            },
            /* 'filter_query' => function (QueryBuilder $qb, string $query, EntityRepository $employeRepository) {
                 if (!$query) {
                     return;
                 }
                 $employeRepository->searchForAutocomplete($qb, $query);
             },*/
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}