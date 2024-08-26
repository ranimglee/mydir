<?php
namespace App\Controller;

use App\Entity\Todo;
use App\Repository\TodoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TodoController extends AbstractController
{

    
    /**
     * @Route("/api/todolist", name="get_todos", methods={"GET"})
     */
    public function getTodos(TodoRepository $todoRepository): JsonResponse
    {
        $todos = $todoRepository->findAll();
        return $this->json($todos);
    }

 /**
 * @Route("/api/todolist/create", name="create_todolist", methods={"POST"})
 */
public function createTodoList(Request $request, TodoRepository $todoRepository): JsonResponse
{
    $data = json_decode($request->getContent(), true);



    $todo = new Todo();
    $todo->setTitle($data['title']);
    $todo->setDescription($data['description']);
    $todo->setIsCompleted($data['isCompleted']);

    try {
        $todo->setCreatedAt(new \DateTime($data['createdAt']));
    } catch (\Exception $e) {
        return $this->json(['error' => 'Invalid date format'], 400);
    }
    
 
    // Save the entity and handle any database errors
    try {
        $todoRepository->save($todo, true);
    } catch (\Exception $e) {
        return $this->json(['error' => 'Failed to save todo: ' . $e->getMessage()], 500);
    }

    return $this->json($todo, 201);
}

    /**
 * @Route("/api/todolist/delete/{id}", name="delete_todolist", methods={"DELETE"})
 */
public function deleteTodoList($id, TodoRepository $todoRepository): JsonResponse
{
    $todo = $todoRepository->find($id);

    if (!$todo) {
        return $this->json(['error' => 'Todo not found'], 404);
    }

    $todoRepository->remove($todo);

    return $this->json(['message' => 'Todo deleted successfully'], 200);
}

/**
 * @Route("/api/todolist/update/{id}", name="update_todo", methods={"PUT"})
 */
public function updateTodo(Request $request, int $id, TodoRepository $todoRepository): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    $todo = $todoRepository->find($id);

    if (!$todo) {
        return new JsonResponse(['error' => 'Todo not found'], JsonResponse::HTTP_NOT_FOUND);
    }

    if (isset($data['isCompleted'])) {
        $todo->setIsCompleted($data['isCompleted']);
    } else {
        return new JsonResponse(['error' => 'Invalid data'], JsonResponse::HTTP_BAD_REQUEST);
    }

    try {
        $todoRepository->save($todo); // Ensure save method handles flush
    } catch (\Exception $e) {
        return new JsonResponse(['error' => 'Failed to update todo: ' . $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
    }

    return new JsonResponse($todo, JsonResponse::HTTP_OK);
}
}

