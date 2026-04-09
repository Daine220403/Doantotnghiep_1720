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

        $userMessage = trim((string) $request->input('message', '')); // đảm bảo luôn là string, tránh null

        if ($userMessage === '') {
            return response()->json([
                'ok' => false,
                'reply' => 'Anh/chị hãy nhập nội dung cần tư vấn giúp em nhé.',
            ], 422);
        }

        // Bước 1: dùng AI phân tích nhanh câu hỏi để lấy intent / ngân sách / điểm đến...
        $analysis = $this->analyzeMessageWithGemini($userMessage, $apiKey);

        $intents = $analysis['intents'] ?? [];
        if (!is_array($intents)) {
            $intents = [];
        }

        $budgetAmount   = $analysis['budget_amount']   ?? null; // VND
        $budgetType     = $analysis['budget_type']     ?? null; // max|min|around|null
        $discountAmount = $analysis['discount_amount'] ?? null; // VND
        $destination    = $analysis['destination']      ?? null;
        $numDays        = $analysis['num_days']         ?? null;

        // Bước 2: lọc tour trong DB phù hợp với phân tích trên
        $toursQuery = Tours::query()->where('status', 'published');

        // Lọc theo ngân sách nếu có
        if (is_numeric($budgetAmount) && (in_array('consult_tour', $intents, true) || in_array('faq_price', $intents, true))) {
            $budgetAmount = (float) $budgetAmount;

            if ($budgetAmount > 0) {
                if ($budgetType === 'max') {
                    $toursQuery->where('base_price_from', '<=', (int) $budgetAmount);
                } elseif ($budgetType === 'min') {
                    $toursQuery->where('base_price_from', '>=', (int) $budgetAmount);
                } else { // around hoặc null => cho phép +-20%
                    $min = (int) ($budgetAmount * 0.8);
                    $max = (int) ($budgetAmount * 1.2);
                    $toursQuery->whereBetween('base_price_from', [$min, $max]);
                }
            }
        }

        // Lọc thêm theo điểm đến nếu AI bắt được destination
        if (is_string($destination) && $destination !== '') {
            $toursQuery->where(function ($q) use ($destination) {
                $q->where('destination_text', 'like', '%' . $destination . '%')
                  ->orWhere('title', 'like', '%' . $destination . '%');
            });
        }

        // Lọc theo số ngày tour nếu có
        if (is_numeric($numDays) && (int) $numDays > 0) {
            $toursQuery->where('duration_days', (int) $numDays);
        }

        // Lấy một số tour sau khi đã lọc để làm ngữ cảnh cho AI
        $tours = $toursQuery
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

        // Một số hướng dẫn bổ sung cho AI dựa trên kết quả phân tích intent
        $extraInstruction = $this->buildExtraInstructionFromAnalysis($analysis);
        $instructionBlock = $extraInstruction !== ''
            ? "\nLƯU Ý NỘI BỘ (ĐỪNG LẶP LẠI NGUYÊN VĂN, CHỈ DÙNG LÀM NGỮ CẢNH):\n{$extraInstruction}\n"
            : '';

        $prompt = <<<PROMPT
Bạn là trợ lý du lịch ảo của một công ty lữ hành. Nhiệm vụ của bạn:
- Tư vấn chọn tour (gợi ý theo điểm đến, ngân sách, thời gian, loại tour trong nước/quốc tế).
- Giải đáp các câu hỏi về chương trình tour, giá, dịch vụ, thanh toán.
- Trò chuyện thân thiện, xưng hô "em" với khách, ưu tiên trả lời ngắn gọn, dễ hiểu.

QUAN TRỌNG:
- Chỉ gợi ý dựa trên danh sách tour bên dưới, không tự bịa tour mới.
- Khi gợi ý tour, hãy nêu tên tour, thời lượng, điểm đến chính, giá từ và đường dẫn (slug) để khách có thể bấm xem chi tiết.
- Nếu câu hỏi không liên quan đến du lịch/tour, hãy trả lời lịch sự và ngắn gọn.

{$instructionBlock}

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

    /**
     * Gọi Gemini để phân tích intent / ngân sách / điểm đến từ câu hỏi của khách.
     * Nếu có lỗi, trả về cấu trúc mặc định an toàn.
     */
    private function analyzeMessageWithGemini(string $userMessage, string $apiKey): array
    {
        $default = [
            'intents' => [],
            'budget_amount' => null,
            'budget_type' => null,
            'discount_amount' => null,
            'destination' => null,
            'num_days' => null,
        ];

        try {
            $analysisPrompt = <<<PROMPT
Bạn là hệ thống phân tích câu hỏi khách hàng về du lịch/tour. NHIỆM VỤ: chỉ trả về MỘT JSON object thuần, không giải thích, không thêm chữ nào khác.

Cho câu hỏi tiếng Việt dưới đây, hãy xác định và trả về JSON với các trường:
- "intents": Mảng các giá trị chọn trong: ["consult_tour","faq_price","faq_schedule","faq_policy","booking_guide","other"]. Có thể có nhiều intent nếu câu hỏi chứa nhiều ý.
- "budget_amount": Số tiền khách nhắc đến dùng làm GIÁ TOUR (đơn vị VND, dạng số nguyên), hoặc null nếu không có.
- "budget_type": "max" | "min" | "around" | null.
- "discount_amount": Số tiền khách nhắc đến dùng làm MỨC GIẢM GIÁ (VND), hoặc null nếu không có.
- "destination": Điểm đến chính (ví dụ "Đà Nẵng", "Phú Quốc"), hoặc null.
- "num_days": Số ngày tour nếu khách có nói (ví dụ "3 ngày 2 đêm" -> 3), hoặc null.

Quy ước xử lý tiền:
- "giá 5tr", "khoảng 5 triệu" -> budget_amount = 5000000, budget_type = "around".
- "tầm 5tr đổ lại", "dưới 5tr" -> budget_amount = 5000000, budget_type = "max".
- "từ 5tr trở lên" -> budget_amount = 5000000, budget_type = "min".
- "giảm 5tr", "giảm giá 5 triệu" -> discount_amount = 5000000.

Ví dụ JSON hợp lệ cần trả về (chỉ minh hoạ cấu trúc):
{"intents":["consult_tour"],"budget_amount":5000000,"budget_type":"around","discount_amount":null,"destination":"Đà Nẵng","num_days":3}

Câu hỏi của khách:
"{$userMessage}"
PROMPT;

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-goog-api-key' => $apiKey,
            ])->post(
                'https://generativelanguage.googleapis.com/v1beta/models/gemini-3-flash-preview:generateContent',
                [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $analysisPrompt],
                            ],
                        ],
                    ],
                ]
            );

            if (!$response->successful()) {
                Log::warning('Gemini chatbot analysis error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return $default;
            }

            $data = $response->json();
            $text = data_get($data, 'candidates.0.content.parts.0.text');

            if (!is_string($text) || trim($text) === '') {
                return $default;
            }

            $decoded = json_decode($text, true);
            if (!is_array($decoded)) {
                return $default;
            }

            return array_merge($default, $decoded);
        } catch (\Throwable $e) {
            Log::error('Chatbot analysis exception', [
                'message' => $e->getMessage(),
            ]);

            return $default;
        }
    }

    /**
     * Sinh thêm hướng dẫn nội bộ cho AI dựa trên kết quả phân tích.
     */
    private function buildExtraInstructionFromAnalysis(array $analysis): string
    {
        $lines = [];

        $intents = $analysis['intents'] ?? [];
        if (!is_array($intents)) {
            $intents = [];
        }

        $budgetAmount   = $analysis['budget_amount']   ?? null;
        $discountAmount = $analysis['discount_amount'] ?? null;
        $destination    = $analysis['destination']      ?? null;
        $numDays        = $analysis['num_days']         ?? null;

        if (is_numeric($budgetAmount) && ((float) $budgetAmount) > 0 && (in_array('consult_tour', $intents, true) || in_array('faq_price', $intents, true))) {
            $lines[] = 'Khách đang quan tâm tới tour với ngân sách khoảng ' . number_format((float) $budgetAmount, 0, ',', '.') . ' VND. Hãy ưu tiên gợi ý các tour phù hợp khoảng giá này.';
        }

        if (is_string($destination) && $destination !== '') {
            $lines[] = 'Khách nhắc tới điểm đến: ' . $destination . '. Nếu phù hợp, hãy ưu tiên các tour đến khu vực này.';
        }

        if (is_numeric($numDays) && (int) $numDays > 0) {
            $lines[] = 'Khách quan tâm tới tour khoảng ' . (int) $numDays . ' ngày. Nếu có, hãy ưu tiên các tour có thời lượng tương ứng.';
        }

        if (in_array('booking_guide', $intents, true)) {
            $lines[] = 'Khách đang hỏi về cách đặt tour / quy trình booking. Hãy mô tả ngắn gọn các bước đặt tour online (chọn tour, điền thông tin, thanh toán, nhận xác nhận).';
        }

        if (in_array('faq_policy', $intents, true)) {
            $lines[] = 'Khách đang hỏi về chính sách đổi/hoàn tour. Hiện em không có dữ liệu chính sách chi tiết trong hệ thống, KHÔNG được tự bịa các con số phần trăm, số ngày hay điều kiện cụ thể; hãy trả lời chung chung và mời khách xem thêm trên website hoặc để lại thông tin để nhân viên hỗ trợ.';
        }

        if (is_numeric($discountAmount) && ((float) $discountAmount) > 0 && in_array('faq_policy', $intents, true)) {
            $lines[] = 'Khách có nhắc tới mức giảm giá khoảng ' . number_format((float) $discountAmount, 0, ',', '.') . ' VND. Do không có dữ liệu khuyến mãi chi tiết, đừng khẳng định có/không chương trình giảm chính xác số tiền đó.';
        }

        return implode("\n", $lines);
    }
}
