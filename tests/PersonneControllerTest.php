<?php


use App\Entity\Personne;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PersonneControllerTest extends WebTestCase
{
    public function testIndex(): void
    {

        // Given
        $client = static::createClient();

        // When
        // Make a request to the index action
        $client->request('GET', '/api/personne/');

        // Then
        // Assert that the response is successful (HTTP 200 status code)
        $this->assertResponseIsSuccessful();

        // Assert that the response content is in JSON format
        $this->assertJson($client->getResponse()->getContent());

    }

    public function testShow(): void
    {
        // Given
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine')->getManager();


        $personne = new Personne();
        $personne->setName('Maher');
        $personne->setAge(400);

        $entityManager->persist($personne);
        $entityManager->flush();

        // When
        $client->request('GET', '/api/personne/' . $personne->getId());

        // Then
        $this->assertResponseIsSuccessful();

        $this->assertJson($client->getResponse()->getContent());

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('age', $data);

        $this->assertEquals($personne->getId(), $data['id']);
        $this->assertEquals($personne->getName(), $data['name']);
        $this->assertEquals($personne->getAge(), $data['age']);
    }

    public function testNew(): void
    {
        // Given
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine')->getManager();

        // When
        $client->request(
            'POST',
            '/api/personne/',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['name' => 'Ahmed', 'age' => 30])
        );

        // Then
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $this->assertJson($client->getResponse()->getContent());

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('Personne created!', $data['message']);

        $personneRepository = $entityManager->getRepository(Personne::class);
        $createdPersonne = $personneRepository->findOneBy(['name' => 'Ahmed']);

        $this->assertInstanceOf(Personne::class, $createdPersonne);
        $this->assertEquals(30, $createdPersonne->getAge());
    }

    public function testEdit(): void
    {
        // Given
        $client = static::createClient();
        $entityManager = $client->getContainer()->get('doctrine')->getManager();

        // Create a Personne entity for testing
        $personne = new Personne();
        $personne->setName('TestPerson');
        $personne->setAge(15);

        $entityManager->persist($personne);
        $entityManager->flush();

        // When
        $client->request(
            'PUT',
            '/api/personne/' . $personne->getId(),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['name' => 'Updated Name', 'age' => 35])
        );

        // Then
        $this->assertResponseIsSuccessful();

        $this->assertJson($client->getResponse()->getContent());

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('Personne updated!', $data['message']);

        $entityManager->refresh($personne);

        $this->assertEquals('Updated Name', $personne->getName());
        $this->assertEquals(35, $personne->getAge());
    }

    // public function testDelete(): void
    // {
    //     // Given
    //     $client = static::createClient();
    //     $entityManager = $client->getContainer()->get('doctrine')->getManager();

    //     $personne = new Personne();
    //     $personne->setName('Manoubi');
    //     $personne->setAge(60);

    //     $entityManager->persist($personne);
    //     $entityManager->flush();

    //     // When
    //     $client->request('DELETE', '/api/personne/' . $personne->getId());

    //     // Then
    //     $this->assertResponseIsSuccessful();

    //     $this->assertJson($client->getResponse()->getContent());

    //     $data = json_decode($client->getResponse()->getContent(), true);

    //     $this->assertArrayHasKey('message', $data);
    //     $this->assertEquals('Personne deleted!', $data['message']);

    //     $deletedPersonne = $entityManager->getRepository(Personne::class)->find($personne->getId());

    //     $this->assertNull($deletedPersonne, 'The Personne entity should be deleted from the database');
    // }
}