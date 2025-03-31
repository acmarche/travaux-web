<?php

namespace AcMarche\Travaux\Form;

use AcMarche\Travaux\Entity\Intervention;
use AcMarche\Travaux\Entity\Security\User;
use AcMarche\Travaux\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InterventionFromMessageType extends AbstractType
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $users = $this->userRepository->getForSearch();
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
                'user_add',
                ChoiceType::class,
                array(
                    'choices' => $users,
                    'label' => 'Demandé par',
                    'required' => false,
                    'attr' => ['class' => 'custom-select my-1 mr-sm-2'],
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
