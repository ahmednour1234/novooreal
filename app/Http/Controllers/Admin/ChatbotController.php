<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Faq;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;


class ChatbotController extends Controller
{
   public function chat(Request $request)
    {
        try {
            $message = trim($request->input('message'));

            if (empty($message)) {
                return response()->json(['reply' => 'يرجى إدخال سؤال.'], 400);
            }

            // البحث عن إجابة في قاعدة البيانات
            $faq = Faq::where('question', 'LIKE', "%$message%")->first();
            if ($faq) {
                return response()->json(['reply' => $faq->answer]);
            }

            // إذا لم يوجد إجابة، نرسل السؤال إلى ChatGPT
            $response = $this->askChatGPT($message);

            return response()->json(['reply' => $response]);

        } catch (\Exception $e) {
            Log::error("Chatbot Error: " . $e->getMessage());
return response()->json([
    'reply' => 'حدث خطأ في الخادم: ' . $e->getMessage()
], 500);
        }
    }

    private function askChatGPT($message)
{
    $client = new \GuzzleHttp\Client();
    $apiKey = env('HUGGINGFACE_API_KEY'); // جلب المفتاح من ملف .env
    $response = $client->post('https://api-inference.huggingface.co/models/facebook/blenderbot-3B', [
        'headers' => [
            'Authorization' => "Bearer $apiKey",
            'Content-Type'  => 'application/json',
        ],
        'json' => [
            'inputs' => $message,
        ],
    ]);

    $data = json_decode($response->getBody(), true);
    return $data[0]['generated_text'] ?? 'عذرًا، لم أتمكن من فهم سؤالك.';
}
 public function getSuggestions(Request $request)
    {
        $query = $request->input('query');

        // جلب الأسئلة المشابهة من قاعدة البيانات
        $suggestions = DB::table('faqs')
            ->where('question', 'LIKE', "%{$query}%")
            ->pluck('question')
            ->take(5) // تحديد عدد النتائج
            ->toArray();

        return response()->json(['suggestions' => $suggestions]);
    }
}

