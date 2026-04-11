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

        // =============================
        // 1. PHÂN TÍCH NHẸ Ở BACKEND
        // =============================
        $message = mb_strtolower($userMessage);

        $budget = null;
        $days = null;
        $destination = null;

        // bắt giá: 5tr, 5 triệu
        if (preg_match('/(\d+)\s*(tr|triệu)/u', $message, $m)) {
            $budget = (int) $m[1] * 1000000;
        }

        // bắt số ngày: 3 ngày
        if (preg_match('/(\d+)\s*ngày/u', $message, $m)) {
            $days = (int) $m[1];
        }

        // bắt địa điểm đơn giản
        $locations = [
            'đà lạt',
            'đà nẵng',
            'nha trang',
            'phú quốc',
            'hà nội',
            'sapa',
            'vũng tàu',
            'huế',
            'quy nhơn',
            'miền tây',
            'cần thơ',
        ];

        foreach ($locations as $loc) {
            if (str_contains($message, $loc)) {
                $destination = $loc;
                break;
            }
        }

        // =============================
        // 2. XỬ LÝ CÂU HỎI MƠ HỒ
        // =============================
        // $isConsultOnly =
        //     str_contains($message, 'tư vấn') ||
        //     str_contains($message, 'gợi ý') ||
        //     str_contains($message, 'nên đi đâu') ||
        //     str_contains($message, 'muốn đi du lịch') ||
        //     str_contains($message, 'alo');

        // if ($isConsultOnly && !$budget && !$days && !$destination) {
        //     return response()->json([
        //         'ok' => true,
        //         'reply' => 'Dạ anh/chị muốn đi đâu, khoảng mấy ngày và ngân sách tầm bao nhiêu ạ? Ví dụ: Đà Lạt 3 ngày khoảng 5 triệu.',
        //     ]);
        // }

        // =============================
        // 3. QUERY DB
        // =============================
        $toursQuery = Tours::query()->where('status', 'published');

        if ($budget) {
            $min = (int) ($budget * 0.8);
            $max = (int) ($budget * 1.2);
            $toursQuery->whereBetween('base_price_from', [$min, $max]);
        }

        if ($destination) {
            $toursQuery->where(function ($q) use ($destination) {
                $q->where('destination_text', 'like', '%' . $destination . '%')
                    ->orWhere('title', 'like', '%' . $destination . '%');
            });
        }

        if ($days) {
            $toursQuery->where('duration_days', $days);
        }

        $tours = $toursQuery
            ->orderBy('base_price_from')
            ->limit(3)
            ->get([
                'title',
                'slug',
                'tour_type',
                'departure_location',
                'destination_text',
                'duration_days',
                'base_price_from',
            ]);

        // fallback nếu lọc quá chặt mà không có tour
        if ($tours->isEmpty()) {
            $tours = Tours::query()
                ->where('status', 'published')
                ->orderBy('base_price_from')
                ->limit(3)
                ->get([
                    'title',
                    'slug',
                    'tour_type',
                    'departure_location',
                    'destination_text',
                    'duration_days',
                    'base_price_from',
                ]);
        }

        // =============================
        // 4. BUILD CONTEXT HTML
        // =============================
        $toursContext = $tours->isEmpty()
            ? 'Hiện chưa có tour phù hợp.'
            : $tours->map(function ($tour, $index) {
                $no = $index + 1;
                $type = $tour->tour_type === 'international' ? 'Quốc tế' : 'Trong nước';
                $duration = $tour->duration_days ? $tour->duration_days . ' ngày' : 'Nhiều ngày';
                $price = number_format((float) $tour->base_price_from, 0, ',', '.');

                return sprintf(
                    '%d. <strong>%s</strong> (%s)<br>
                📍 %s<br>
                ⏱ %s<br>
                💰 Giá từ %s VND<br>
                <a href="/tours/%s" target="_blank"
                   style="display:inline-block;margin-top:6px;padding:6px 10px;background:#2563eb;color:#fff;border-radius:6px;font-size:13px;text-decoration:none;font-weight:600;">
                   Xem chi tiết
                </a>',
                    $no,
                    e($tour->title),
                    e($type),
                    e($tour->destination_text ?: 'Đa điểm'),
                    e($duration),
                    $price,
                    e($tour->slug)
                );
            })->implode('<br><br>');

        // =============================
        // 5. PROMPT
        // =============================
        $prompt = <<<PROMPT
Bạn là trợ lý du lịch ảo của một công ty lữ hành.

Nhiệm vụ:
- Tư vấn chọn tour theo điểm đến, ngân sách, thời gian.
- Trả lời ngắn gọn, tự nhiên, thân thiện, xưng "em".
- Chỉ gợi ý dựa trên danh sách tour được cung cấp.
- Không tự bịa tour mới.
- Nếu khách hỏi quá chung chung, hãy hỏi lại ngắn gọn để làm rõ nhu cầu.
- Nếu gợi ý tour, hãy giữ nguyên thẻ HTML <a href="/tours/...">Xem chi tiết</a>.

DANH SÁCH TOUR:
{$toursContext}

CÂU HỎI CỦA KHÁCH:
"{$userMessage}"

Hãy trả lời bằng tiếng Việt, tối đa 2-4 câu.
PROMPT;

        try {
            $response = Http::timeout(60)
                ->retry(2, 1000)
                ->withHeaders([
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

            if (!$response->successful()) {
                Log::warning('Gemini chatbot error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return response()->json([
                    'ok' => true,
                    'reply' => $toursContext,
                ]);
            }

            $data = $response->json();
            $reply = data_get($data, 'candidates.0.content.parts.0.text');

            if (!is_string($reply) || trim($reply) === '') {
                $reply = $toursContext;
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
                'ok' => true,
                'reply' => $toursContext,
            ]);
        }
    }
}
