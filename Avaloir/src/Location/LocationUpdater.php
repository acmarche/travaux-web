<?php


namespace AcMarche\Avaloir\Location;

use AcMarche\Avaloir\Entity\Avaloir;
use AcMarche\Avaloir\Repository\AvaloirRepository;
use AcMarche\Avaloir\Repository\RueRepository;
use Exception;

class LocationUpdater
{
    public function __construct(
        private AvaloirRepository $avaloirRepository,
        private RueRepository $rueRepository,
        private LocationReverseInterface $locationReverse,
    ) {
    }

    /**
     * @param Avaloir $avaloir
     * @return void
     * @throws Exception
     */
    public function updateRueAndLocalite(Avaloir $avaloir): void
    {
        $result = $this->locationReverse->reverse($avaloir->getLatitude(), $avaloir->getLongitude());
        if ($this->isResultOk($result)) {
            $road = $this->locationReverse->getRoad();
            if ($road) {
                if ($number = $this->locationReverse->getHouseNumber()) {
                    $road = $road.' '.$number;
                }
                $avaloir->setRue($road);
                $rue = $this->rueRepository->findOneByRue($road);
                if ($rue !== null) {
                    $avaloir->setLocalite($rue->getVillage());
                } else {
                    $avaloir->setLocalite($this->locationReverse->getLocality());
                }
                $this->avaloirRepository->flush();
            } else {
                throw new Exception('road non trouve dans api. Avaloir id '.$avaloir->getId());
            }
        } else {
            throw new Exception('result pas OK');
        }
    }

    protected function isResultOk(array $result): bool
    {
        if (isset($result['status']) && $result['status'] == 'OK') {
            return true;
        }

        return !isset($result['error']);
    }
}
