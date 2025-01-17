<?php

namespace App\Controller;

use App\Entity\UserBeer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CartController extends AbstractController
{
    private $httpClient;
    private $entityManager;

    public function __construct(HttpClientInterface $httpClient, EntityManagerInterface $entityManager)
    {
        $this->httpClient = $httpClient;
        $this->entityManager = $entityManager;
    }

    #[Route('/cart', name: 'app_cart')]
    public function index(SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);
        $cartWithData = [];
        $total = 0;

        foreach ($cart as $id => $quantity) {
            $response = $this->httpClient->request(
                'GET',
                'https://ubeer-production.up.railway.app/beers/' . $id
            );
            
            $beer = $response->toArray();
            $cartWithData[] = [
                'beer' => $beer,
                'quantity' => $quantity
            ];
            
            $total += $beer['price'] * $quantity;
        }

        return $this->render('cart/index.html.twig', [
            'items' => $cartWithData,
            'total' => $total
        ]);
    }

    #[Route('/cart/add/{id}', name: 'app_cart_add')]
    public function add($id, SessionInterface $session, Request $request): Response
    {
        // Ajouter au panier de session
        $cart = $session->get('cart', []);
        $quantity = $request->query->get('quantity', 1);

        if (!empty($cart[$id])) {
            $cart[$id] += $quantity;
        } else {
            $cart[$id] = $quantity;
        }

        $session->set('cart', $cart);

        // Si l'utilisateur est connecté, lier la bière à son compte
        if ($this->getUser()) {
            $userBeer = new UserBeer();
            $userBeer->setUser($this->getUser());
            $userBeer->setBeerId($id);
            
            $this->entityManager->persist($userBeer);
            $this->entityManager->flush();
        }

        $this->addFlash('success', 'La bière a été ajoutée au panier');
        
        $referer = $request->headers->get('referer');
        return $this->redirect($referer ?: $this->generateUrl('app_home'));
    }

    #[Route('/cart/remove/{id}', name: 'app_cart_remove')]
    public function remove($id, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);

        if (!empty($cart[$id])) {
            unset($cart[$id]);
        }

        $session->set('cart', $cart);

        // Si l'utilisateur est connecté, supprimer le lien avec la bière
        if ($this->getUser()) {
            $userBeer = $this->entityManager->getRepository(UserBeer::class)->findOneBy([
                'user' => $this->getUser(),
                'beerId' => $id
            ]);
            
            if ($userBeer) {
                $this->entityManager->remove($userBeer);
                $this->entityManager->flush();
            }
        }

        $this->addFlash('success', 'La bière a été retirée du panier');
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/update/{id}', name: 'app_cart_update', methods: ['POST'])]
    public function update($id, Request $request, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);
        $quantity = (int) $request->request->get('quantity');

        if ($quantity > 0) {
            $cart[$id] = $quantity;
        } else {
            unset($cart[$id]);
            
            // Si l'utilisateur est connecté et la quantité est 0, supprimer le lien
            if ($this->getUser()) {
                $userBeer = $this->entityManager->getRepository(UserBeer::class)->findOneBy([
                    'user' => $this->getUser(),
                    'beerId' => $id
                ]);
                
                if ($userBeer) {
                    $this->entityManager->remove($userBeer);
                    $this->entityManager->flush();
                }
            }
        }

        $session->set('cart', $cart);
        return $this->redirectToRoute('app_cart');
    }
}
