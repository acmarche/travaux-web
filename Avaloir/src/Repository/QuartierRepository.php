<?php

namespace AcMarche\Avaloir\Repository;

use AcMarche\Avaloir\Entity\Quartier;
use AcMarche\Travaux\Repository\OrmCrudTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Quartier|null find($id, $lockMode = null, $lockVersion = null)
 * @method Quartier|null findOneBy(array $criteria, array $orderBy = null)
 * @method Quartier[]    findAll()
 * @method Quartier[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuartierRepository extends ServiceEntityRepository
{
    use OrmCrudTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quartier::class);
    }

    public function getQbl(): QueryBuilder
    {
        return $this->createQueryBuilder('q')
            ->orderBy('q.nom');
    }

    /**
     * @return Quartier[]
     */
    public function getForSearch(): array
    {
        $qb = $this->createQueryBuilder('q');

        $qb->orderBy('q.nom');
        $query = $qb->getQuery();

        $results = $query->getResult();
        $quartiers = array();

        foreach ($results as $quartier) {
            $quartiers[$quartier->getNom()] = $quartier->getId();
        }

        return $quartiers;
    }

    /**
     * @param $args
     * @return Quartier[]
     */
    public function search($args): array
    {
        $nom = $args['nom'] ?? null;

        $qb = $this->createQueryBuilder('q');
        $qb->leftJoin('q.rues', 'r', 'WITH');
        $qb->addSelect('r');

        if ($nom != null) {
            $qb->andWhere('q.nom LIKE :mot')
                ->setParameter('mot', '%'.$nom.'%');
        }

        $qb->addOrderBy('q.nom', 'ASC');
        $qb->addOrderBy('r.nom', 'ASC');

        $query = $qb->getQuery();

        //echo  $query->getSQL();

        $results = $query->getResult();

        return $results;
    }
}
