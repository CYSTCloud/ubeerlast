<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TestBeerController extends AbstractController
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/beer/{id}/edit', name: 'app_beer_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        // Récupérer la bière actuelle
        $response = $this->httpClient->request(
            'GET',
            'https://ubeer-production.up.railway.app/beers/' . $id
        );

        $beer = $response->toArray();

        // Si c'est une requête POST, traiter la modification
        if ($request->isMethod('POST')) {
            $updatedData = [
                'beer' => $request->request->get('beer'),
                'price' => (float) $request->request->get('price'),
                'brewery_id' => (int) $request->request->get('brewery_id'),
                'imageUrl' => $request->request->get('imageUrl')
            ];

            try {
                $response = $this->httpClient->request(
                    'PATCH',
                    'https://ubeer-production.up.railway.app/beers/' . $id,
                    [
                        'json' => $updatedData,
                        'headers' => [
                            'Content-Type' => 'application/merge-patch+json'
                        ]
                    ]
                );

                if ($response->getStatusCode() === 200) {
                    $this->addFlash('success', 'La bière a été mise à jour avec succès.');
                    return $this->redirectToRoute('app_home');
                }
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
            }
        }

        return $this->render('beer/edit.html.twig', [
            'beer' => $beer
        ]);
    }
}
