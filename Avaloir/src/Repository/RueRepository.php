<?php

namespace AcMarche\Avaloir\Repository;

use AcMarche\Travaux\Repository\OrmCrudTrait;
use Doctrine\ORM\QueryBuilder;
use AcMarche\Avaloir\Entity\Quartier;
use AcMarche\Avaloir\Entity\Rue;
use AcMarche\Avaloir\Entity\Village;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Rue|null find($id, $lockMode = null, $lockVersion = null)
 * @method Rue|null findOneBy(array $criteria, array $orderBy = null)
 * method Rue[]    findAll()
 * @method Rue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RueRepository extends ServiceEntityRepository
{
    use OrmCrudTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rue::class);
    }

    /**
     * @return Rue[]
     */
    public function findAll(): array
    {
        return $this->findBy(array(), array('nom' => 'ASC'));
    }

    /**
     * @param $args
     * @return Rue[]
     */
    public function search($args): array
    {
        $nom = $args['nom'] ?? null;
        $village = $args['village'] ?? null;
        $quartier = $args['quartier'] ?? null;

        $qb = $this->createQueryBuilder('rue');
        $qb->leftJoin('rue.quartier', 'quartier', 'WITH');
        $qb->addSelect('quartier');

        if ($nom != null) {
            $qb->andWhere('rue.nom LIKE :mot')
                ->setParameter('mot', '%'.$nom.'%');
        }

        if ($village) {
            $qb->andWhere('rue.village = :village')
                ->setParameter('village', $village);
        }

        if ($quartier) {
            $qb->andWhere('quartier.id = :quartier')
                ->setParameter('quartier', $quartier);
        }

        $qb->addOrderBy('rue.village', 'ASC');
        $qb->addOrderBy('rue.nom', 'ASC');

        $query = $qb->getQuery();

        return $query->getResult();
    }

    public function getForList(): QueryBuilder
    {
        return $this
            ->createQueryBuilder('r')
            ->addOrderBy('r.village', 'ASC')
            ->addOrderBy('r.nom', 'ASC');
    }

    /**
     * @return Village[]
     */
    public function getVillages(): array
    {
        $qb = $this->createQueryBuilder('r');
        $qb->addOrderBy('r.nom', 'ASC');

        $query = $qb->getQuery();

        //echo  $query->getSQL();

        $villages = array();
        $results = $query->getResult();

        foreach ($results as $result) {
            $villages[$result->getVillage()] = $result->getVillage();
        }

        ksort($villages);

        return $villages;
    }


    /**
     * @param bool $groupBy
     * @return Rue[]
     */
    public function getByQuartier(Quartier $quartier): array
    {
        $qb = $this->createQueryBuilder('rue');

        $qb->leftJoin('rue.quartier', 'quartier', 'WITH');
        $qb->addSelect('quartier');

        $qb->andWhere('quartier.id = :quartier')
            ->setParameter('quartier', $quartier);
        $qb->addOrderBy('rue.nom', 'ASC');

        $query = $qb->getQuery();
        $results = $query->getResult();

        return $results;
    }

    public function findOneByRue(string $road): ?Rue
    {
        return $this->createQueryBuilder('rue')
            ->andWhere('rue.nom = :road')
            ->setParameter('road', $road)->getQuery()->getOneOrNullResult();
    }

}
