<?php

namespace AcMarche\Avaloir\Service;

use AcMarche\Avaloir\Entity\Quartier;
use AcMarche\Avaloir\Repository\RueRepository;

class TokenService
{
    public function __construct(private RueRepository $rueRepository)
    {
    }

    public function destinatairesToArray(Quartier $quartier): array
    {
        $rues = $this->rueRepository->getByQuartier($quartier);
        $values = [];
        $i = 0;
        foreach ($rues as $rue) {
            $values[$i]['id'] = $rue->getId();
            $values[$i]['name'] = $rue->getNom();
            ++$i;
        }

        return $values;
    }
}