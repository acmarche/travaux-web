<?php


namespace AcMarche\Avaloir\Image;

use ImagickException;
use Imagick;
use AcMarche\Avaloir\Entity\Avaloir;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

/**
 * Class ImageService
 * @package AcMarche\Avaloir\Image
 */
class ImageService
{
    public function __construct(private ParameterBagInterface $parameterBag, private UploaderHelper $uploaderHelper)
    {
    }

    /**
     * @param $imagePath
     * @param $angle
     * @param $color
     * @throws ImagickException
     */
    function rotateImage(Avaloir $avaloir): bool
    {
        $imagick = null;
        if ($this->getOrientationImage($avaloir) == 6) {
            $imagick = new Imagick($this->getPath($avaloir));
            $color = $imagick->getImageBackgroundColor();
            $imagick->rotateimage($color, 90);
        }

        return $imagick->writeImage($this->getPath($avaloir));
    }

    function getOrientationImage(Avaloir $avaloir): int
    {
        $imagick = new Imagick($this->getPath($avaloir));

        return $imagick->getImageOrientation();
    }

    function getPath(Avaloir $avaloir): string
    {
        return $this->parameterBag->get('ac_marche_travaux_dir_public').$this->uploaderHelper->asset(
                $avaloir,
                'imageFile'
            );
    }
}
