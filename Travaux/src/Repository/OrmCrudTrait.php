<?php

namespace AcMarche\Travaux\Repository;

use Doctrine\ORM\EntityManagerInterface;

trait OrmCrudTrait
{
    /**
     * @var EntityManagerInterface
     */
    protected $_em;

    public function insert(object $object): void
    {
        $this->persist($object);
        $this->flush();
    }

    public function persist(object $intervention): void
    {
        $this->_em->persist($intervention);
    }

    public function flush(): void
    {
        $this->_em->flush();
    }

    public function remove(object $getIntervention): void
    {
        $this->_em->remove($getIntervention);
        $this->flush();
    }

}