<?php

namespace App\Controller;

use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/books')]
class BookController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    #[Route('', methods: ['GET'])]
    public function getBooks(): JsonResponse
    {
        $books = $this->entityManager->getRepository(Book::class)->findAll();
        return new JsonResponse($this->serializer->serialize($books, 'json'), JsonResponse::HTTP_OK, [], true);
    }

    #[Route('', methods: ['POST'])]
    public function addBook(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!isset($data['title'], $data['author'], $data['year']) || !is_numeric($data['year']) || $data['year'] <= 0) {
            return new JsonResponse(['message' => 'Dados inválidos'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $book = new Book();
        $book->setTitle($data['title']);
        $book->setAuthor($data['author']);
        $book->setYear((int)$data['year']);

        // Validação da entidade
        $errors = $this->validator->validate($book);
        if (count($errors) > 0) {
            return new JsonResponse(['message' => 'Dados inválidos'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $this->entityManager->persist($book);
        $this->entityManager->flush();

        return new JsonResponse($this->serializer->serialize($book, 'json'), JsonResponse::HTTP_CREATED, [], true);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function updateBook(int $id, Request $request): JsonResponse
    {
        $book = $this->entityManager->getRepository(Book::class)->find($id);
        if (!$book) {
            return new JsonResponse(['message' => 'Livro não encontrado'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (isset($data['title'])) $book->setTitle($data['title']);
        if (isset($data['author'])) $book->setAuthor($data['author']);
        if (isset($data['year']) && is_numeric($data['year']) && $data['year'] > 0) {
            $book->setYear((int)$data['year']);
        }

        $this->entityManager->flush();

        return new JsonResponse($this->serializer->serialize($book, 'json'), JsonResponse::HTTP_OK, [], true);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function deleteBook(int $id): JsonResponse
    {
        $book = $this->entityManager->getRepository(Book::class)->find($id);
        if (!$book) {
            return new JsonResponse(['message' => 'Livro não encontrado'], JsonResponse::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($book);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Livro removido com sucesso'], JsonResponse::HTTP_OK);
    }
}
