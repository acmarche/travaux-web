<?php


namespace AcMarche\Avaloir\Location;


use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * https://developers.google.com/maps/documentation/geocoding/start
 * Class GoogleReverse
 * @package AcMarche\Avaloir\Location
 */
class GoogleReverse implements LocationReverseInterface
{
    private string $url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=40.714224,-73.961452&key=YOUR_API_KEY';
    private string $baseUrl;
    private HttpClientInterface $client;
    private array $result = [];

    public function __construct(private string $apiKeyGoogle)
    {
        $this->baseUrl = 'https://maps.googleapis.com/maps/api/geocode/json';
        $this->client = HttpClient::create();
    }

    /**
     * @param $latitude
     * @param $longitude
     */
    public function reverse($latitude, $longitude): array
    {
        try {
            $request = $this->client->request(
                'GET',
                $this->baseUrl,
                [
                    'query' => [
                        //'location_type' => 'ROOFTOP',
                        'result_type' => 'street_address',
                        'key' => $this->apiKeyGoogle,
                        'latlng' => $latitude.','.$longitude,
                    ],
                ]
            );

            $this->result = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

            return $this->result;
        } catch (ClientException|TransportExceptionInterface $e) {
            return ['error' => true, 'message' => $e->getMessage()];
        }
    }

    public function getRoad(): ?string
    {
        $results = $this->result['results'];
        $first = $results[0];

        return $first['address_components'][1]['long_name'];
    }

    public function getLocality(): ?string
    {
        $results = $this->result['results'];
        $first = $results[0];

        return $first['address_components'][2]['long_name'];
    }

    public function getHouseNumber(): ?string
    {
        return null;
    }
}
