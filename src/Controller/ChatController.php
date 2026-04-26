<?php

namespace App\Controller;

use App\Service\AiAssistantService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/chat', name: 'chat_')]
class ChatController extends AbstractController
{
    public function __construct(
        private readonly AiAssistantService $assistant,
    ) {}

    /**
     * Page principale du chat.
     */
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $meta = $this->assistant->getDatabaseMeta();

        return $this->render('chat/index.html.twig', [
            'db_name'    => $meta['database'],
            'db_tables'  => $meta['table_count'],
        ]);
    }

    /**
     * Endpoint AJAX : reçoit une question, retourne la réponse JSON.
     */
   #[Route('/ask', name: 'ask', methods: ['POST'])]
public function ask(Request $request, SessionInterface $session): JsonResponse
{
    $body     = json_decode($request->getContent(), true) ?? [];
    $question = trim($body['question'] ?? '');

    if ($question === '') {
        return $this->json(['error' => 'Question vide.'], Response::HTTP_BAD_REQUEST);
    }

    // Récupérer le CIN de l'utilisateur connecté
    $user = $this->getUser();
    $cin  = $user?->getCin();

    $history = $session->get('chat_history', []);

    try {
        $result = $this->assistant->answer($question, $history, $cin);

        $history[] = ['role' => 'user',      'content' => $question];
        $history[] = ['role' => 'assistant',  'content' => $result['analysis']];
        $session->set('chat_history', array_slice($history, -20));

        return $this->json($result);
    } catch (\Throwable $e) {
        return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
    /**
     * Efface l'historique de conversation.
     */
    #[Route('/clear', name: 'clear', methods: ['POST'])]
    public function clear(SessionInterface $session): JsonResponse
    {
        $session->remove('chat_history');

        return $this->json(['status' => 'ok']);
    }
    #[Route('/health', name: 'chat_health', methods: ['GET'])]
public function health(): JsonResponse
{
    $meta = $this->assistant->getDatabaseMeta();
    return $this->json(['status' => 'ok', 'database' => $meta['database'], 'tables' => $meta['table_count']]);
}
}