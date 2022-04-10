<?php
/**
 * Created by PhpStorm.
 * User: jfsenechal
 * Date: 20/03/19
 * Time: 14:06
 */

namespace AcMarche\Stock\Service;

use AcMarche\Avaloir\Entity\Avaloir;
use AcMarche\Avaloir\Entity\Commentaire;
use AcMarche\Avaloir\Entity\DateNettoyage;
use AcMarche\Stock\Entity\Categorie;
use AcMarche\Stock\Entity\Produit;
use AcMarche\Travaux\Entity\Security\User;
use DateTimeInterface;
use Liip\ImagineBundle\Service\FilterService;
use stdClass;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class SerializeApi
{
    private string $root;
    private string $urlBase;

    public function __construct(
        private UploaderHelper $uploaderHelper,
        private RequestStack $requestStack,
        private FilterService $filterService,
        private ParameterBagInterface $parameterBag
    ) {
        $this->root = $this->parameterBag->get('ac_marche_travaux_dir_public');
        $this->urlBase = $this->getUrl();
    }

    public function getUrl(): string
    {
        if ($this->requestStack->getMainRequest() !== null) {
            return $this->requestStack->getMainRequest()->getSchemeAndHttpHost();
        }

        return '';
    }

    /**
     * @param Avaloir[] $avaloirs
     */
    public function serializeAvaloirs(iterable $avaloirs): array
    {
        $data = [];
        foreach ($avaloirs as $avaloir) {
            $std = $this->serializeAvaloir($avaloir);
            $data[] = $std;
        }

        return $data;
    }

    public function serializeAvaloir(Avaloir $avaloir): stdClass
    {
        $std = new stdClass();
        $std->id = $avaloir->getId();
        $std->idReferent = $avaloir->getId();
        $std->latitude = $avaloir->getLatitude();
        $std->longitude = $avaloir->getLongitude();
        $std->rue = $avaloir->getRue();
        $std->localite = $avaloir->getLocalite();
        $std->description = $avaloir->getDescription();
        $std->createdAt = $avaloir->getCreatedAt()->format(DateTimeInterface::RFC3339);//'Y-m-d H:m'
        if ($avaloir->getImageName()) {
            $pathImg = $this->uploaderHelper->asset($avaloir, 'imageFile');
            $fullPath = $this->root.$pathImg;
            if (is_readable($fullPath)) {
                $thumb = $this->filterService->getUrlOfFilteredImage($pathImg, 'avaloir_heighten_filter');
                $std->imageUrl = $thumb !== '' && $thumb !== '0' ? $thumb : $this->urlBase.$this->uploaderHelper->asset(
                        $avaloir,
                        'imageFile'
                    );
            }
        }

        return $std;
    }

    /**
     * @param Produit[] $produits
     */
    public function serializeProduits(iterable $produits): array
    {
        $data = [];
        foreach ($produits as $produit) {
            $std = new stdClass();
            $std->id = $produit->getId();
            $std->nom = $produit->getNom();
            $std->categorie_id = $produit->getCategorie()->getId();
            $std->description = $produit->getDescription();
            $std->quantite = $produit->getQuantite();
            $std->reference = $produit->getReference();
            $std->image = '';
            $data[] = $std;
        }

        return $data;
    }

    /**
     * @param Categorie[] $categories
     */
    public function serializeCategorie(iterable $categories): array
    {
        $data = [];
        foreach ($categories as $categorie) {
            $std = new stdClass();
            $std->id = $categorie->getId();
            $std->nom = $categorie->getNom();
            $std->description = $categorie->getDescription();
            $data[] = $std;
        }

        return $data;
    }

    public function serializeUser(User $user): stdClass
    {
        $token = "123456";
        $std = new stdClass();
        $std->id = $user->getId();
        $std->nom = $user->getNom();
        $std->prenom = $user->getPrenom();
        $std->email = $user->getEmail();
        $std->token = $token;

        $user->setToken($token);

        return $std;
    }

    /**
     * @param DateNettoyage[] $dates
     */
    public function serializeDates(array $dates): array
    {
        $data = [];
        foreach ($dates as $date) {
            $std = $this->serializeDate($date);
            $data[] = $std;
        }

        return $data;
    }

    /**
     * @param Commentaire[] $commentaires
     */
    public function serializeCommentaires(array $commentaires): array
    {
        $data = [];
        foreach ($commentaires as $commentaire) {
            $std = $this->serializeCommentaire($commentaire);
            $data[] = $std;
        }

        return $data;
    }

    public function serializeDate(DateNettoyage $date): stdClass
    {
        $std = new stdClass();
        $std->id = $date->getId();
        $std->idReferent = $date->getId();
        $std->avaloirId = $date->getAvaloir()->getId();
        $std->date = $date->getJour()->format('Y-m-d');
        $std->createdAt = $date->getJour()->format(DateTimeInterface::RFC3339);

        return $std;
    }

    public function serializeCommentaire(Commentaire $commentaire): stdClass
    {
        $std = new stdClass();
        $std->id = $commentaire->getId();
        $std->idReferent = $commentaire->getId();
        $std->avaloirId = $commentaire->getAvaloir()->getId();
        $std->content = $commentaire->getContent();
        $std->createdAt = $commentaire->getCreatedAt()->format(DateTimeInterface::RFC3339);

        return $std;
    }
}
