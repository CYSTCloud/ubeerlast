<?php

namespace App\Controller\Admin;

use App\Entity\UserBeer;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

class UserBeerCrudController extends AbstractCrudController
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public static function getEntityFqcn(): string
    {
        return UserBeer::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Bière d\'utilisateur')
            ->setEntityLabelInPlural('Bières des utilisateurs')
            ->setPageTitle('index', 'Gérer les bières des utilisateurs')
            ->setPageTitle('edit', fn (UserBeer $userBeer) => sprintf('Modifier la bière de %s', $userBeer->getUser()->getFirstname()))
            ->setPageTitle('new', 'Ajouter une bière à un utilisateur');
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('user')
                ->setLabel('Utilisateur')
                ->formatValue(function ($value, $entity) {
                    return $entity->getUser() ? sprintf('%s', $entity->getUser()->getFirstname() . ' ' . $entity->getUser()->getLastname()) : '';
                }),
            TextField::new('beerName', 'Nom de la bière')
                ->setFormTypeOption('mapped', false)
                ->formatValue(function ($value, $entity) {
                    try {
                        $response = $this->httpClient->request(
                            'GET',
                            'https://ubeer-production.up.railway.app/beers/' . $entity->getBeerId(),
                            [
                                'headers' => [
                                    'Accept' => 'application/json',
                                ],
                                'verify_peer' => false,
                                'verify_host' => false
                            ]
                        );
                        
                        if ($response->getStatusCode() === 200) {
                            $beer = $response->toArray();
                            if (isset($beer['beer'])) {
                                $entity->setBeerName($beer['beer']);
                                return $beer['beer'];
                            }
                        }
                        return 'Bière #' . $entity->getBeerId();
                    } catch (\Exception $e) {
                        error_log('Erreur lors de la récupération de la bière: ' . $e->getMessage());
                        return 'Bière #' . $entity->getBeerId() . ' (Erreur: ' . $e->getMessage() . ')';
                    }
                }),
            IdField::new('beerId')
                ->setLabel('ID de la bière')
                ->onlyOnForms(),
        ];

        return $fields;
    }
}
