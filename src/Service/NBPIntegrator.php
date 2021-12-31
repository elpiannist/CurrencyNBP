<?php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpClient\CurlHttpClient;
use App\Repository\CurrencyRepository;
use App\Entity\Currency;

class NBPIntegrator
{
    const API_URL="https://api.nbp.pl/api/exchangerates";

    private CurlHttpClient $httpClient;
    private EntityManagerInterface $em;

    public function __construct(CurrencyRepository $currencyRepository, EntityManagerInterface $em)
    {
        $this->currencyRepository = $currencyRepository;
        $this->httpClient = new CurlHttpClient();
        $this->em = $em;
    }
    public function fetchCurrencies($table = 'A', $field = 'mid') : string
    {
        $URL = self::API_URL. '/tables/'. $table;
        $response = $this->httpClient->request("GET", $URL, ['headers'=>['Accept'=>'application-json']])->getContent();
        $data  = json_decode($response, true);
        var_dump($data);
        foreach ($data[0]['rates'] as $responseCurrency)
        {
            if ( is_null ($currency = $this->currencyRepository->findByCurrencyCode($responseCurrency['code'] )))
            {
                $currency = new Currency();
                $currency->setCurrencyCode($responseCurrency['code']);
                $currency->setName($responseCurrency['currency']);
                $currency->setExchangeRate($responseCurrency[$field]);
            }
            else
            {
                $currency->setExchangeRate($responseCurrency['mid']);
            }
            $this->em->persist($currency);
        }
        $this->em->flush();
        return "asd";
    }
}