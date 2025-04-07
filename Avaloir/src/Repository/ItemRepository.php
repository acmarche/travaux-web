<?php

namespace AcMarche\Avaloir\Repository;

use AcMarche\Avaloir\Entity\Item;
use AcMarche\Travaux\Doctrine\OrmCrudTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Item|null find($id, $lockMode = null, $lockVersion = null)
 * @method Item|null findOneBy(array $criteria, array $orderBy = null)
 * method Item[]    findAll()
 * @method Item[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemRepository extends ServiceEntityRepository
{
    use OrmCrudTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Item::class);
    }

    /**
     * @return Item[]
     */
    public function findAll(): array
    {
        return $this->findBy(array(), array('id' => 'DESC'));
    }

    public function findByLatitudeAndLongitude(mixed $latitude, mixed $longitude): ?Item
    {
        return $this->createQueryBuilder('item')
            ->andWhere('item.latitude = :latitude')
            ->setParameter('latitude', $latitude)
            ->andWhere('item.longitude = :longitude')
            ->setParameter('longitude', $longitude)
            ->getQuery()
            ->getOneOrNullResult();
    }

}
