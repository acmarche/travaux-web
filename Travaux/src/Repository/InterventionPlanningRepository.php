<?php

namespace AcMarche\Travaux\Repository;

use AcMarche\Travaux\Entity\CategoryPlanning;
use AcMarche\Travaux\Entity\InterventionPlanning;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method InterventionPlanning|null find($id, $lockMode = null, $lockVersion = null)
 * @method InterventionPlanning|null findOneBy(array $criteria, array $orderBy = null)
 * @method InterventionPlanning[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InterventionPlanningRepository extends ServiceEntityRepository
{
    use OrmCrudTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InterventionPlanning::class);
    }

    /**
     * @return InterventionPlanning[]
     */
    public function findAll(): array
    {
        return $this->findBy(array(), array('id' => 'ASC'));
    }

    /**
     * @return array|InterventionPlanning[]
     */
    public function findPlanningByDayAndCategory(DateTime $date, ?CategoryPlanning $categoryPlanning = null): array
    {
        $qbl = $this->createQbl();

        if ($categoryPlanning) {
            $qbl->andWhere('intervention_planning.category = :category')
                ->setParameter('category', $categoryPlanning);
        }

        return $qbl
            ->andWhere('intervention_planning.dates LIKE :date')
            ->setParameter('date', '%'.$date->format('Y-m-d').'%')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array|InterventionPlanning[]
     */
    public function findByCategory(?CategoryPlanning $categoryPlanning): array
    {
        $qbl = $this->createQbl();
        if ($categoryPlanning) {
            $qbl->andWhere('intervention_planning.category = :category')
                ->setParameter('category', $categoryPlanning);
        }

        return $qbl
            ->getQuery()
            ->getResult();
    }

    private function createQbl(): QueryBuilder
    {
        return $this->createQueryBuilder('intervention_planning')
            ->leftJoin('intervention_planning.employes', 'employes', 'WITH')
            ->leftJoin('intervention_planning.category', 'category', 'WITH')
            ->addSelect('employes', 'category');
    }

}
