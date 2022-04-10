<?php
/**
 * Created by PhpStorm.
 * User: jfsenechal
 * Date: 22/01/18
 * Time: 14:05
 */

namespace AcMarche\Travaux\Service;

use AcMarche\Travaux\Entity\Intervention;
use AcMarche\Travaux\Entity\Suivi;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SuiviService
{
    public function __construct(protected EntityManagerInterface $entityManger, protected TokenStorageInterface $tokenStorage)
    {
    }

    public function initSuivi(Intervention $intervention): Suivi
    {
        $suivi = new Suivi();
        $suivi->setIntervention($intervention);

        return $suivi;
    }

    public function newSuivi(Intervention $intervention, $message = null)
    {
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return false;
        }

        $user = $token->getUser();

        if ($message) {
            $suivis = $this->initSuivi($intervention);
            $suivis->setDescriptif($message);
            $suivis->setUserAdd($user);

            $this->entityManger->persist($suivis);
            $this->entityManger->flush();
        }
    }
}
