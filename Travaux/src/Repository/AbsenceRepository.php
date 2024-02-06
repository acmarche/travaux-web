<?php

namespace AcMarche\Travaux\Repository;

use AcMarche\Travaux\Doctrine\OrmCrudTrait;
use AcMarche\Travaux\Entity\Absence;
use AcMarche\Travaux\Entity\CategoryPlanning;
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

    public function findByDateAndCategory(\DateTimeInterface $date, ?CategoryPlanning $categoryPlanning = null): array
    {
        $qb = $this->createQueryBuilder('absence')
            ->leftJoin('absence.employe', 'employe', 'WITH')
            ->leftJoin('employe.categories', 'categories', 'WITH')
            ->addSelect('employe', 'categories')
            ->andWhere(':date BETWEEN absence.date_begin AND absence.date_end')
            ->setParameter('date', $date->format('Y-m-d'));

        if ($categoryPlanning) {
            $qb->andWhere(':category MEMBER OF employe.categories')
                ->setParameter('category', $categoryPlanning->getId());
        }

        return $qb->getQuery()->getResult();
    }

}
