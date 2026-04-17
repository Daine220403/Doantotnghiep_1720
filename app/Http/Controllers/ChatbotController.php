<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\Tours;

class ChatbotController extends Controller
{
  public function handle(Request $request)
  {
    $apiKey = config('services.gemini.api_key');

    if (!$apiKey) {
      return response()->json([
        'ok' => false,
        'reply' => 'Xin lỗi, chatbot đang được cấu hình. Vui lòng thử lại sau.',
      ], 500);
    }

    $userMessage = trim((string) $request->input('message', ''));

    if ($userMessage === '') {
      return response()->json([
        'ok' => false,
        'reply' => 'Anh/chị hãy nhập nội dung cần tư vấn giúp em nhé.',
      ], 422);
    }

    // =============================
    // 1. PARSE AI (CÓ CACHE)
    // =============================
    $parsed = Cache::remember(
      'parse_' . md5($userMessage),
      300, // cache 5 phút
      fn() => $this->parseUserMessage($userMessage)
    );

    $intent = $parsed['intent'];
    $destination = $parsed['destination'];
    $days = $parsed['days'];
    $budget = $parsed['budget'];

    // =============================
    // 2. HANDLE INTENT (NO AI)
    // =============================
    if ($intent === 'count_tours') {
      $total = Tours::where('status', 'published')->count();

      return response()->json([
        'ok' => true,
        'reply' => "Hiện tại bên em có khoảng {$total} tour đang mở bán ạ.",
      ]);
    }

    if ($intent === 'system_info') {
      $domestic = Tours::where('tour_type', 'domestic')->count();
      $international = Tours::where('tour_type', 'international')->count();

      return response()->json([
        'ok' => true,
        'reply' => "Hiện có {$domestic} tour trong nước và {$international} tour quốc tế ạ.",
      ]);
    }

    if ($intent === 'consult') {
      return response()->json([
        'ok' => true,
        'reply' => "Dạ anh/chị muốn đi đâu, mấy ngày và ngân sách khoảng bao nhiêu để em tư vấn chuẩn hơn ạ?",
      ]);
    }

    // =============================
    // 3. QUERY DB
    // =============================
    $toursQuery = Tours::query()->where('status', 'published');

    if ($destination) {
      $toursQuery->where(function ($q) use ($destination) {
        $q->where('destination_text', 'like', "%{$destination}%")
          ->orWhere('title', 'like', "%{$destination}%");
      });
    }

    if ($days) {
      $toursQuery->where('duration_days', $days);
    }

    if ($budget) {
      $min = (int) ($budget * 0.7);
      $max = (int) ($budget * 1.3);

      $toursQuery->whereBetween('base_price_from', [$min, $max]);
    }

    $totalMatched = $toursQuery->count();

    $tours = $toursQuery
      ->orderBy('base_price_from')
      ->limit(10)
      ->get([
        'title',
        'slug',
        'base_price_from',
      ]);

    // =============================
    // 4. BUILD CONTEXT
    // =============================
    $toursContext = $tours->isEmpty()
      ? "Không tìm thấy tour phù hợp."
      : "Tìm thấy {$totalMatched} tour phù hợp (một số gợi ý):\n\n" .
      $tours->map(function ($tour, $i) {
        $price = number_format($tour->base_price_from, 0, ',', '.');

        return ($i + 1) . ". {$tour->title}\n💰 {$price} VND\n👉 /tours/{$tour->slug}";
      })->implode("\n\n");

    // =============================
    // 5. PROMPT AI
    // =============================
    $prompt = <<<PROMPT
          Bạn là trợ lý du lịch chuyên nghiệp.

          QUY TẮC:
          - Danh sách tour chỉ là một phần nhỏ từ hệ thống.
          - Không được nói hệ thống chỉ có số tour đang hiển thị.
          - Trả lời tự nhiên, thân thiện, xưng "em".
          - Ưu tiên gợi ý tour từ dữ liệu cung cấp.
          - Khi có link tour, hãy dùng thẻ <a href="..." target="_blank" 
              style="color:blue;text-decoration:underline;"
          > Xem chi tiết </a>

          - Nếu khách hỏi tour KHÔNG có trong danh sách:
            + Không được trả lời "không có" một cách cứng nhắc.
            + Hãy thông báo nhẹ nhàng là hiện chưa có tour đó.
            + Sau đó gợi ý các tour tương tự hoặc gần giống (cùng khu vực, cùng loại trải nghiệm, hoặc phổ biến nhất).
            + Luôn cố gắng giữ cuộc hội thoại tiếp tục.

          DỮ LIỆU:
          {$toursContext}

          CÂU HỎI:
          "{$userMessage}"

          Trả lời ngắn gọn 2-4 câu.
          PROMPT;

    try {
      $response = Http::timeout(30)
        ->retry(2, 500)
        ->withHeaders([
          'Content-Type' => 'application/json',
          'x-goog-api-key' => $apiKey,
        ])
        ->post(
          'https://generativelanguage.googleapis.com/v1beta/models/gemini-3-flash-preview:generateContent',
          [
            'contents' => [
              [
                'parts' => [
                  ['text' => $prompt],
                ],
              ],
            ],
          ]
        );

      if (!$response->successful()) {
        Log::warning('Gemini error', [
          'status' => $response->status(),
          'body' => $response->body(),
        ]);

        return response()->json([
          'ok' => false,
          'reply' => 'Hệ thống đang bận, anh/chị vui lòng thử lại sau ạ.',
        ]);
      }

      $reply = data_get($response->json(), 'candidates.0.content.parts.0.text');

      if (!is_string($reply) || trim($reply) === '') {
        $reply = "Em tìm được một số tour phù hợp ạ:\n\n" . $toursContext;
      }

      return response()->json([
        'ok' => true,
        'reply' => $reply,
      ]);
    } catch (\Throwable $e) {
      if (str_contains($e->getMessage(), '429')) {
        return response()->json([
          'ok' => false,
          'reply' => 'Hệ thống đang quá tải, anh/chị vui lòng thử lại sau vài giây ạ.',
        ]);
      }

      Log::error('Chatbot exception', [
        'message' => $e->getMessage(),
      ]);

      return response()->json([
        'ok' => true,
        'reply' => $toursContext,
      ]);
    }
  }

  private function parseUserMessage($message)
  {
    $apiKey = config('services.gemini.api_key');

    $prompt = <<<PROMPT
          Phân tích câu người dùng và trả về JSON:

          {
            "intent": "count_tours | system_info | search_tour | consult | other",
            "destination": "... hoặc null",
            "days": số hoặc null,
            "budget": số VND hoặc null
          }

          Chỉ trả JSON.

          Câu: "{$message}"
          PROMPT;

    try {
      $response = Http::withHeaders([
        'Content-Type' => 'application/json',
        'x-goog-api-key' => $apiKey,
      ])->post(
        'https://generativelanguage.googleapis.com/v1beta/models/gemini-3-flash-preview:generateContent',
        [
          'contents' => [
            [
              'parts' => [
                ['text' => $prompt],
              ],
            ],
          ],
        ]
      );

      $text = data_get($response->json(), 'candidates.0.content.parts.0.text');

      $text = trim($text);
      $text = preg_replace('/```json|```/', '', $text);

      $json = json_decode($text, true);

      if (!$json) {
        return $this->fallbackParse();
      }

      $validIntents = ['count_tours', 'system_info', 'search_tour', 'consult', 'other'];

      return [
        'intent' => in_array($json['intent'] ?? '', $validIntents)
          ? $json['intent']
          : 'search_tour',

        'destination' => $json['destination'] ?? null,
        'days' => is_numeric($json['days'] ?? null) ? (int)$json['days'] : null,
        'budget' => is_numeric($json['budget'] ?? null) ? (int)$json['budget'] : null,
      ];
    } catch (\Throwable $e) {
      return $this->fallbackParse();
    }
  }

  private function fallbackParse()
  {
    return [
      'intent' => 'search_tour',
      'destination' => null,
      'days' => null,
      'budget' => null,
    ];
  }
}
