<?php

namespace AcMarche\Travaux\Repository;

use AcMarche\Travaux\Entity\Absence;
use AcMarche\Travaux\Entity\Employe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Absence|null find($id, $lockMode = null, $lockVersion = null)
 * @method Absence|null findOneBy(array $criteria, array $orderBy = null)
 * @method Absence[]    findAll()
 * @method Absence[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AbsenceRepository extends ServiceEntityRepository
{
    use OrmCrudTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Absence::class);
    }

    /**
     * @return Absence[]
     */
    public function findAllOrderd(): array
    {
        return $this->findBy(array(), array('date_begin' => 'ASC'));
    }

    /**
     * @return Absence[]
     */
    public function findByEmploye(Employe $employe): array
    {
        return $this->createQueryBuilder('absence')
            ->andWhere('absence.employe = :employe')
            ->setParameter('employe', $employe)
            ->getQuery()
            ->getResult();
    }

}
