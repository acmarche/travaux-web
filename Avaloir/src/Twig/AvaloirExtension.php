<?php

namespace AcMarche\Avaloir\Twig;

use AcMarche\Avaloir\Location\StreetView;
use Exception;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AvaloirExtension extends AbstractExtension
{
    public function __construct(private StreetView $streetView)
    {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('street_view', fn(string $latitude, string $longitude) => $this->StreetView($latitude, $longitude)),
        ];
    }

    /**
     * @return array|mixed|string
     */
    public function StreetView(string $latitude, string $longitude)
    {
        $content = $this->streetView->getPhoto($latitude, $longitude);
        try {
            $img = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
            if (is_array($img) && $img['error']) {
                return $img['message'];
            }
        } catch (Exception) {
        }

        return base64_encode($content);
    }
}
