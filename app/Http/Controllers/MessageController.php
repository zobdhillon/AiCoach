<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MessageController extends Controller
{
    public function store(Request $request, Conversation $conversation)
    {
        $validated = $request->validate([
            'message_text' => 'required|string|max:2000',
        ]);

        $conversation->messages()->create([
            'role'    => 'user',
            'content' => $validated['message_text'],
        ]);

        $userMessageCount = $conversation->messages()
            ->where('role', 'user')
            ->count();

        $autoComplete = $userMessageCount >= 10;

        if ($autoComplete) {
            $scores = $this->scoreConversation($conversation);

            $conversation->update([
                'scores' => $scores,
                'status' => 'completed',
            ]);

            return response()->json([
                'message'       => null,
                'auto_complete' => true,
                'scores'        => $scores,
                'status'        => 'completed',
            ]);
        }

        $aiMessage = $this->getAiReply($conversation);

        return response()->json([
            'message'       => $aiMessage,
            'auto_complete' => false,
        ]);
    }

    private function getAiReply(Conversation $conversation): array
    {
        $scenario  = $conversation->scenario;
        $history   = $conversation->messages()->orderBy('created_at')->get();

        $formatted = [];

        $formatted[] = [
            'role'    => 'system',
            'content' => $scenario->system_prompt . "\n\n" .
                "CRITICAL RULES — NEVER BREAK THESE:\n" .
                "- Speak in natural, conversational dialogue ONLY. Never write emails, letters, or formal documents.\n" .
                "- You are in a live spoken conversation. Respond the way a real person talks.\n" .
                "- NEVER use placeholders like [Candidate Name] or [Salary Amount]. Use real specifics.\n" .
                "- NEVER break character under any circumstances.\n" .
                "- NEVER mention being an AI, a language model, or a simulation.\n" .
                "- Keep responses to 1-3 sentences max. Short and natural.\n" .
                "- NEVER write greetings like 'Dear...' or sign offs like 'Best regards'.\n" .
                "- If you are an HR manager, speak like one in a real meeting — direct and professional.\n" .
                "- Always respond to what the user just said. Never ignore their last message.",
        ];

        foreach ($history as $msg) {
            if ($msg->role === 'system') continue;
            $formatted[] = [
                'role'    => $msg->role,
                'content' => $msg->content,
            ];
        }

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . env('GROQ_API_KEY'),
                    'Content-Type'  => 'application/json',
                ])
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model'       => env('GROQ_MODEL'),
                    'messages'    => $formatted,
                    'temperature' => 0.4,
                    'max_tokens'  => 150,
                ]);

            $content = $response->successful()
                ? $response->json('choices.0.message.content')
                : 'Sorry, I had trouble responding. Please try again.';
        } catch (\Exception $e) {
            $content = 'Connection error. Please try again.';
        }

        $saved = $conversation->messages()->create([
            'role'    => 'assistant',
            'content' => $content,
        ]);

        return [
            'id'      => $saved->id,
            'role'    => $saved->role,
            'content' => $saved->content,
        ];
    }

    private function scoreConversation(Conversation $conversation): array
    {
        $messages = $conversation->messages()->orderBy('created_at')->get();

        $transcript = $messages->map(function ($msg) {
            $label = $msg->role === 'user' ? 'Candidate' : 'Interviewer';
            return "{$label}: {$msg->content}";
        })->implode("\n");

        $prompt = "You are an expert communication coach. Review this conversation transcript and evaluate the user's (Candidate) performance.\n\n" .
            "TRANSCRIPT:\n{$transcript}\n\n" .
            "Score the candidate on these 4 dimensions out of 100:\n" .
            "- clarity: How clearly did they communicate?\n" .
            "- confidence: How confident and assertive were they?\n" .
            "- objective: How well did they achieve the scenario goal?\n" .
            "- adaptability: How well did they respond to pushback?\n\n" .
            "Calculate final as the average of all 4 scores.\n" .
            "Write 2-3 sentences of specific, actionable feedback.\n\n" .
            "CRITICAL: Return ONLY valid JSON, no markdown, no backticks:\n" .
            '{"final":85,"clarity":90,"confidence":80,"objective":85,"adaptability":85,"feedback":"Your feedback here."}';

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . env('GROQ_API_KEY'),
                    'Content-Type'  => 'application/json',
                ])
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model'       => env('GROQ_MODEL'),
                    'messages'    => [['role' => 'user', 'content' => $prompt]],
                    'temperature' => 0.1,
                    'max_tokens'  => 300,
                ]);

            if ($response->successful()) {
                $raw    = $response->json('choices.0.message.content');
                $clean  = preg_replace('/```json|```/', '', $raw);
                $scores = json_decode(trim($clean), true);

                if ($scores && isset($scores['final'])) {
                    return $scores;
                }
            }
        } catch (\Exception $e) {
            // fall through to fallback
        }

        return [
            'final'        => 0,
            'clarity'      => 0,
            'confidence'   => 0,
            'objective'    => 0,
            'adaptability' => 0,
            'feedback'     => 'Evaluation could not be completed. Please try again.',
        ];
    }
}
