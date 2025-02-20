<?php

namespace App\Controllers;

use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class BookController
{
    private $db;

    public function __construct()
    {
        $this->db = new PDO('sqlite:database.db', '', '', [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        ]);
    }

    public function index(Request $request, Response $response, $args)
    {
        $books = $this->fetchAllBooks();
        return $this->respondWithJson($response, $books);
    }

    public function show(Request $request, Response $response, $args)
    {
        $book = $this->fetchBookById($args['id']);

        if (!$book) {
            return $this->respondWithError($response, 'Book not found', 404);
        }

        return $this->respondWithJson($response, $book);
    }

    public function create(Request $request, Response $response)
    {
        $data = $request->getParsedBody();

        if (empty($data['title']) || empty($data['author']) || empty($data['description'])) {
            return $this->respondWithError($response, 'Invalid input', 400);
        }

        $this->insertBook($data['title'], $data['author'], $data['description']);

        $createdBook = $this->fetchBookById($this->db->lastInsertId());

        return $this->respondWithJson($response->withStatus(201), $createdBook);
    }

    public function update(Request $request, Response $response, $args)
    {
        $data = $request->getParsedBody();

        if (empty($data['title']) || empty($data['author']) || empty($data['description'])) {
            return $this->respondWithError($response, 'Invalid input', 400);
        }

        $book = $this->fetchBookById($args['id']);

        if (!$book) {
            return $this->respondWithError($response, 'Book not found', 404);
        }

        $this->updateBook($args['id'], $data['title'], $data['author'], $data['description']);

        $updatedBook = $this->fetchBookById($args['id']);

        return $this->respondWithJson($response, $updatedBook);
    }

    public function delete(Request $request, Response $response, $args)
    {
        $book = $this->fetchBookById($args['id']);

        if (!$book) {
            return $this->respondWithError($response, 'Book not found', 404);
        }

        $deletedBook = $this->fetchBookById($args['id']);

        $this->deleteBook($args['id']);

        return $this->respondWithJson($response->withStatus(200), $deletedBook);
    }

    private function fetchAllBooks()
    {
        $result = $this->db->query('SELECT rowid AS id, title, author, description FROM books');
        return $result->fetchAll();
    }

    private function fetchBookById($id)
    {
        $result = $this->db->prepare('SELECT rowid AS id, title, author, description FROM books WHERE rowid = :id');
        $result->execute(['id' => $id]);
        return $result->fetch();
    }

    private function insertBook($title, $author, $description)
    {
        $result = $this->db->prepare('INSERT INTO books (title, author, description) VALUES (:title, :author, :description)');
        $result->execute(['title' => $title, 'author' => $author, 'description' => $description]);
    }

    private function updateBook($id, $title, $author, $description)
    {
        $result = $this->db->prepare('UPDATE books SET title = :title, author = :author, description = :description WHERE rowid = :id');
        $result->execute(['title' => $title, 'author' => $author, 'description' => $description, 'id' => $id]);
    }

    private function deleteBook($id)
    {
        $result = $this->db->prepare('DELETE FROM books WHERE rowid = :id');
        $result->execute(['id' => $id]);
    }

    private function respondWithJson(Response $response, $data)
    {
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    }

    private function respondWithMessage(Response $response, $message, $status)
    {
        $response->getBody()->write(json_encode(['message' => $message]));
        return $response->withStatus($status)->withHeader('Content-Type', 'application/json');
    }

    private function respondWithError(Response $response, $error, $status)
    {
        $response->getBody()->write(json_encode(['error' => $error]));
        return $response->withStatus($status)->withHeader('Content-Type', 'application/json');
    }
}
