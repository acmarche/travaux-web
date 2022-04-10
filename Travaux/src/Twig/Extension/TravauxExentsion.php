<?php

namespace AcMarche\Travaux\Twig\Extension;

use AcMarche\Travaux\Entity\Document;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TravauxExentsion extends AbstractExtension
{
    private ?string $path = null;

    public function __construct(private ParameterBagInterface $parameterBag, private RouterInterface $router)
    {
    }

    /**
     * @Override
     */
    public function getFilters(): array
    {
        return array(
            new TwigFilter('apptravaux_download', fn(Document $document) => $this->downloader($document)),
        );
    }

    public function getFunctions(): array
    {
        return array(
            new TwigFunction('routeExists', fn($name) => $this->routeExists($name)),
        );
    }

    public function downloader(Document $document): string
    {
        $this->path = $this->parameterBag->get('ac_marche_travaux.download.directory');
        $intervention = $document->getIntervention();
        $directory = $this->path."/".$intervention->getId();
        return $directory.'/'.$document->getFileName();
    }

    public function routeExists($name): bool
    {
        return null !== $this->router->getRouteCollection()->get($name);
    }
}
