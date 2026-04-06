<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Tours;

class ChatbotController extends Controller
{
    public function handle(Request $request)
    {
        $apiKey = config('services.gemini.api_key');

        if (!$apiKey) {
            return response()->json([
                'ok' => false,
                'reply' => 'Xin lỗi, chatbot đang được cấu hình. Anh/chị vui lòng thử lại sau.',
            ], 500);
        }

        $userMessage = trim((string) $request->input('message', ''));

        if ($userMessage === '') {
            return response()->json([
                'ok' => false,
                'reply' => 'Anh/chị hãy nhập nội dung cần tư vấn giúp em nhé.',
            ], 422);
        }

        // Lấy một số tour nổi bật để gợi ý / làm ngữ cảnh cho AI
        $tours = Tours::query()
            ->where('status', 'published')
            ->orderBy('base_price_from')
            ->limit(8)
            ->get([
                'title',
                'slug',
                'tour_type',
                'departure_location',
                'destination_text',
                'duration_days',
                'base_price_from',
            ]);

        $toursContext = $tours->isEmpty()
            ? 'Hiện không có tour nào trong danh sách.'
            : $tours->map(function ($tour, $index) {
                $no = $index + 1;
                $type = $tour->tour_type === 'international' ? 'Quốc tế' : 'Trong nước';
                $duration = $tour->duration_days ? $tour->duration_days . ' ngày' : 'Nhiều ngày';
                $price = number_format((float) $tour->base_price_from, 0, ',', '.');

                $slug = $tour->slug;

                return sprintf(
                    '%d. <strong>%s</strong> (%s) - %s, điểm đến: %s, giá từ %s VND. ' .
                    '<a href="/tours/%s" target="_blank" class="text-sky-600 underline">Xem chi tiết tour</a>',
                    $no,
                    e($tour->title),
                    $type,
                    $duration,
                    $tour->destination_text ?: 'Đa điểm',
                    $price,
                    $slug
                );
            })->implode("\n");

        $prompt = <<<PROMPT
Bạn là trợ lý du lịch ảo của một công ty lữ hành. Nhiệm vụ của bạn:
- Tư vấn chọn tour (gợi ý theo điểm đến, ngân sách, thời gian, loại tour trong nước/quốc tế).
- Giải đáp các câu hỏi về chương trình tour, giá, dịch vụ, thanh toán.
- Trò chuyện thân thiện, xưng hô "em" với khách, ưu tiên trả lời ngắn gọn, dễ hiểu.

QUAN TRỌNG:
- Chỉ gợi ý dựa trên danh sách tour bên dưới, không tự bịa tour mới.
- Khi gợi ý tour, hãy nêu tên tour, thời lượng, điểm đến chính, giá từ và đường dẫn (slug) để khách có thể bấm xem chi tiết.
- Nếu câu hỏi không liên quan đến du lịch/tour, hãy trả lời lịch sự và ngắn gọn.

DANH SÁCH MỘT SỐ TOUR HIỆN CÓ (đã kèm sẵn thẻ HTML <a href="/tours/...">Xem chi tiết tour</a>, hãy giữ nguyên các link này trong câu trả lời của bạn):
{$toursContext}

CÂU HỎI / YÊU CẦU CỦA KHÁCH HÀNG:
"{$userMessage}"

Hãy trả lời bằng tiếng Việt, tối đa 2-4 câu, tập trung vào việc tư vấn rõ ràng và thân thiện.
PROMPT;

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $apiKey, // dùng header chuẩn mới
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

            if (!$response->successful()) {
                Log::warning('Gemini chatbot error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return response()->json([
                    'ok' => false,
                    'reply' => 'Xin lỗi, hệ thống đang bận. Anh/chị vui lòng thử lại sau ít phút.',
                ], 502);
            }

            $data = $response->json();
            $reply = data_get($data, 'candidates.0.content.parts.0.text');

            if (!is_string($reply) || trim($reply) === '') {
                $reply = 'Xin lỗi, em chưa hiểu rõ câu hỏi. Anh/chị có thể diễn đạt lại giúp em với ạ?';
            }

            return response()->json([
                'ok' => true,
                'reply' => $reply,
            ]);
        } catch (\Throwable $e) {
            Log::error('Chatbot exception', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'reply' => 'Xin lỗi, hệ thống đang gặp lỗi. Anh/chị vui lòng thử lại sau ít phút.',
            ], 500);
        }
    }
}
