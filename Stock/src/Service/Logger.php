<?php
/**
 * Created by PhpStorm.
 * User: jfsenechal
 * Date: 20/03/19
 * Time: 10:54
 */

namespace AcMarche\Stock\Service;

use AcMarche\Stock\Entity\Log;
use AcMarche\Stock\Entity\Produit;
use AcMarche\Stock\Repository\LogRepository;
use Symfony\Bundle\SecurityBundle\Security;

class Logger
{
    public function __construct(private LogRepository $logRepository, private Security $security)
    {
    }

    public function log(Produit $produit, int $quantite): void
    {
        $log = new Log();
        $log->setNom($produit->getNom());
        $log->setQuantite($quantite);
        $user = $this->security->getUser();
        $username = $user === null ? "smartphone" : $user->getUserIdentifier();

        $log->setUser($username);
        //$log->setCreatedAt(new \DateTime());
        $this->logRepository->insert($log);
    }
}