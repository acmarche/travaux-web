<?php

namespace AcMarche\Travaux\Form\Security;

use AcMarche\Travaux\Entity\Security\User;
use AcMarche\Travaux\Repository\LdapRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UtilisateurType extends AbstractType
{
    public function __construct(private readonly LdapRepository $ldapRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', ChoiceType::class, [
                'label' => 'Sélectionnez un utilisateur',
                'choices' => array_flip($this->ldapRepository->getEntries()),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            array(
                'data_class' => User::class,
            )
        );
    }
}
