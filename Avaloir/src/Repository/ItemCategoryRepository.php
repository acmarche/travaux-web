<?php

namespace AcMarche\Avaloir\Repository;

use AcMarche\Avaloir\Entity\ItemCategory;
use AcMarche\Travaux\Doctrine\OrmCrudTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ItemCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method ItemCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method ItemCategory[]    findAll()
 * @method ItemCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemCategoryRepository extends ServiceEntityRepository
{
    use OrmCrudTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ItemCategory::class);
    }

    /**
     * @return ItemCategory[]
     */
    public function findAllOrdered(): array
    {
        return $this->findBy(array(), array('id' => 'DESC'));
    }


}