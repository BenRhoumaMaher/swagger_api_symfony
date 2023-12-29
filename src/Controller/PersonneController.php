<?php

namespace App\Controller;

use App\Entity\Personne;
use App\Repository\PersonneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

#[Route('/api/personne', name: 'app_personne')]
class PersonneController extends AbstractController
{
    private $entityManager;
    private $personneRepository;
    public function __construct(EntityManagerInterface $entityManager, PersonneRepository $personneRepository)
    {
        $this->entityManager = $entityManager;
        $this->personneRepository = $personneRepository;
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Return list of Persons ',
        content: new Model(type: Personne::class)
    )]
    #[OA\Tag(name: 'List Of Persons')]
    public function index(): JsonResponse
    {
        $personnes = $this->personneRepository->findAll();

        $data = [];
        foreach ($personnes as $personne) {
            $data[] = [
                'id' => $personne->getId(),
                'name' => $personne->getName(),
                'age' => $personne->getAge(),
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    #[ParamConverter('personne', class: 'App\Entity\Personne')]
    #[OA\Response(
        response: 200,
        description: 'Return One Person\s Infos',
        content: new Model(type: Personne::class)
    )]
    #[OA\Tag(name: 'Person\s Infos')]
    public function show(Personne $personne): JsonResponse
    {
        $data = [
            'id' => $personne->getId(),
            'name' => $personne->getName(),
            'age' => $personne->getAge(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/', name: 'new', methods: ['POST'])]
    #[OA\Response(
        response: 200,
        description: 'Return the creation of a new Personne',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Personne::class))
        )
    )]
    #[OA\RequestBody(
        description: 'Send the name and the age of the Personne',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'name', type:'string'),
                new OA\Property(property: 'age', type:'integer'),

            ]
        )
    )]
    #[OA\Tag(name: 'Create new Personne')]
    public function new(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $personne = new Personne();
        $personne->setName($data['name']);
        $personne->setAge($data['age']);

        $this->entityManager->persist($personne);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Personne created!'], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'edit', methods: ['PUT'])]
    #[OA\Response(
        response: 200,
        description: 'Return modification of a person',
        content: new Model(type: Personne::class)
    )]

    #[OA\RequestBody(
        required: true,
        description: "Send fields of creation of a personne",
        content: [new OA\JsonContent(
            properties: [
                new OA\Property(property: 'name', type:'string'),
                new OA\Property(property: 'age', type:'integer'),

            ]
        )]
    )]
    #[OA\Tag(name: 'Update an existing Person')]
    public function edit(Request $request, Personne $personne): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $personne->setName($data['name']);
        $personne->setAge($data['age']);

        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Personne updated!'], Response::HTTP_OK);
    }



    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[OA\Response(
        response: 200,
        description: 'Delet of a person ',
        content: new Model(type: Personne::class)
    )]
    #[OA\Tag(name: 'Delete Person')]
    public function delete(Personne $personne): JsonResponse
    {
        $this->entityManager->remove($personne);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Personne deleted!'], Response::HTTP_OK);
    }
}
