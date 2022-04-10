<?php

namespace AcMarche\Avaloir\Form;

use AcMarche\Avaloir\Entity\Quartier;
use AcMarche\Avaloir\Entity\Rue;
use AcMarche\Avaloir\Repository\QuartierRepository;
use AcMarche\Avaloir\Repository\VillageRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RueType extends AbstractType
{
    public function __construct(private VillageRepository $villageRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add(
                'village',
                ChoiceType::class,
                array(
                    'required' => true,
                    'choices' => $this->villageRepository->getForSearch(),
                    'attr' => ['class' => 'custom-select my-1 mr-sm-2'],
                )
            )
            ->add(
                'quartier',
                EntityType::class,
                array(
                    'class' => Quartier::class,
                    'query_builder'=>function(QuartierRepository $quartierRepository) {
                        $quartierRepository->getQbl();
                    },
                    'required' => false,
                    'attr' => ['class' => 'custom-select my-1 mr-sm-2'],
                )
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            array(
                'data_class' => Rue::class,
            )
        );
    }
}
