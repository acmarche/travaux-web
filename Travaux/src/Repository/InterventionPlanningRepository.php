<?php

namespace AcMarche\Travaux\Repository;

use AcMarche\Travaux\Doctrine\OrmCrudTrait;
use AcMarche\Travaux\Entity\CategoryPlanning;
use AcMarche\Travaux\Entity\InterventionPlanning;
use AcMarche\Travaux\Planning\DateProvider;
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

    public function __construct(ManagerRegistry $registry, private DateProvider $dateProvider)
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
     * @param string $yearMonth
     * @param CategoryPlanning|null $categoryPlanning
     * @return InterventionPlanning[]
     */
    public function findByMonthAndCategory(string $yearMonth, ?CategoryPlanning $categoryPlanning): array
    {
        $date = $this->dateProvider->createDateFromYearMonth($yearMonth);
        $days = $this->dateProvider->daysOfMonth($date);

        $interventions = [[]];
        foreach ($days as $date) {
            $interventions[] = $this->findPlanningByDayAndCategory($date->toDateTime(), $categoryPlanning);
        }

        return array_merge(...$interventions);
    }

    /**
     * @param int $year
     * @param CategoryPlanning|null $categoryPlanning
     * @return InterventionPlanning[]
     */
    public function findByYearAndCategory(int $year, ?CategoryPlanning $categoryPlanning): array
    {
        $days = $this->dateProvider->allDaysOfYear($year);
        $interventions = [[]];
        foreach ($days as $date) {
            $interventions[] = $this->findPlanningByDayAndCategory($date->toDateTime(), $categoryPlanning);
        }

        return array_merge(...$interventions);
    }

    private function createQbl(): QueryBuilder
    {
        return $this->createQueryBuilder('intervention_planning')
            ->leftJoin('intervention_planning.employes', 'employes', 'WITH')
            ->leftJoin('intervention_planning.category', 'category', 'WITH')
            ->addSelect('employes', 'category');
    }

}
