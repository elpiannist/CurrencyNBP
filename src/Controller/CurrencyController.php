<?php

namespace App\Controller;
use App\Service\NBPIntegrator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class CurrencyController extends AbstractController
{
    

    #[Route('/currency', name: 'currency')]
    public function index(NBPIntegrator $nBPIntegrator): Response
    {
        $nBPIntegrator->fetchCurrencies();
        return $this->render('currency/index.html.twig', [
            'controller_name' => 'CurrencyController',
        ]);
    }
}
