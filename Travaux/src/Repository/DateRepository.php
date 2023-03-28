<?php

namespace AcMarche\Travaux\Repository;

use AcMarche\Travaux\Entity\DateEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DateEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method DateEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method DateEntity[]    findAll()
 * @method DateEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DateRepository extends ServiceEntityRepository
{
    use OrmCrudTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DateEntity::class);
    }
}
