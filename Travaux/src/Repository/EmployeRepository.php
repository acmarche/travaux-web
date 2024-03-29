<?php

namespace AcMarche\Travaux\Repository;

use AcMarche\Travaux\Doctrine\OrmCrudTrait;
use AcMarche\Travaux\Entity\CategoryPlanning;
use AcMarche\Travaux\Entity\Employe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Employe|null find($id, $lockMode = null, $lockVersion = null)
 * @method Employe|null findOneBy(array $criteria, array $orderBy = null)
 * @method Employe[]    findAll()
 * @method Employe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmployeRepository extends ServiceEntityRepository
{
    use OrmCrudTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Employe::class);
    }

    /**
     * @return Employe[]
     */
    public function findAllOrdered(): array
    {
        return $this->createQb()->addOrderBy('employe.nom')->getQuery()->getResult();
    }

    /**
     * @return Employe[]
     */
    public function searchForAutocomplete(
        ?string $query,
        ?\DateTimeInterface $dateSelected = null,
        ?int $categoryPlanning = null
    ): array {

        $queryBuilder = $this->createQb();

        if ($categoryPlanning) {
         /*   $queryBuilder->andWhere(':category MEMBER OF employe.categories')
                ->setParameter('category', $categoryPlanning);*/
        }
        if ($dateSelected) {
            //todo remove absent
        }
        if ($query) {
            $queryBuilder
                ->andWhere('employe.nom LIKE :name OR employe.prenom LIKE :name')
                ->setParameter('name', '%'.$query.'%');
        }

        return $queryBuilder
            ->orderBy('employe.nom')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param CategoryPlanning $categoryPlanning
     * @return Employe[]
     */
    public function findByCategory(CategoryPlanning $categoryPlanning): array
    {
        return $this->createQb()
            ->andWhere(':category MEMBER OF employe.categories')
            ->setParameter('category', $categoryPlanning)->orderBy('employe.nom')
            ->getQuery()
            ->getResult();
    }

    private function createQb(): QueryBuilder
    {
        return $this->createQueryBuilder('employe')
            ->leftJoin('employe.absences', 'absences', 'WITH')
            ->leftJoin('employe.categories', 'categories', 'WITH')
            ->addSelect('absences', 'categories');
    }

}
