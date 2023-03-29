<?php

namespace AcMarche\Travaux\Repository;

use AcMarche\Travaux\Entity\CategoryPlanning;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CategoryPlanning|null find($id, $lockMode = null, $lockVersion = null)
 * @method CategoryPlanning|null findOneBy(array $criteria, array $orderBy = null)
 * @method CategoryPlanning[]    findAll()
 * @method CategoryPlanning[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryPlanningRepository extends ServiceEntityRepository
{
    use OrmCrudTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CategoryPlanning::class);
    }

    /**
     * @return CategoryPlanning[]
     */
    public function findAllOrdered(): array
    {
        return $this->findBy(array(), array('name' => 'ASC'));
    }

    public function getQblForList(): QueryBuilder
    {
        return $this->createQueryBuilder('category_planning')
            ->addOrderBy('category_planning.name');
    }

}
