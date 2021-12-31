<?php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Repository\CurrencyRepository;
use App\Entity\Currency;

class NBPIntegrator
{
    const API_URL="https://api.nbp.pl/api/exchangerates";

    private CurlHttpClient $httpClient;
    private EntityManagerInterface $em;
    private ValidatorInterface $validator;

    public function __construct(CurrencyRepository $currencyRepository, EntityManagerInterface $em, ValidatorInterface $validator)
    {
        $this->currencyRepository = $currencyRepository;
        $this->httpClient = new CurlHttpClient();
        $this->em = $em;
        $this->validator = $validator;
    }
    public function fetchCurrencies($table = 'A', $field = 'mid')
    {
        $URL = self::API_URL. '/tables/'. $table;
        $response = $this->httpClient->request("GET", $URL, ['headers'=>['Accept'=>'application-json']])->getContent();
        $data  = json_decode($response, true);
        $currencies = $data[0]['rates'];
        foreach ($currencies as $responseCurrency)
        {
            if (! $this->array_keys_exists(['code', 'currency', $field], $responseCurrency))
            {
                return false;
            }

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

            $errors = $this->validator->validate($currency);
            
            if (count($errors) > 0 )
            {
                return false;
            }

            $this->em->persist($currency);
        }
        $this->em->flush();
        return true;
    }

    private function array_keys_exists(array $keys, array $arr) {
        return !array_diff_key(array_flip($keys), $arr);
    }
}