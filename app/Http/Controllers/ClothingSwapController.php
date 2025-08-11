<?php

namespace App\Http\Controllers;

use App\Services\YouCamService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Support\Facades\RateLimiter;
use Carbon\Carbon;

class ClothingSwapController extends Controller
{
    private $baseUrl = 'https://yce-api-01.perfectcorp.com';
    private $clientId = '596poNOaOFqn6ctvEdwU7JQDZLB5Kzy4'; 
    private $privateKeyPath = '/var/www/html/storage/app/keys/private_key.pem'; 

   
    public function swapClothing(Request $request)
    {
       

       
        




       
       
     

        try {

             $key = 'swap-'.auth()->guard('customers')->user()?->id;
             $limit = 10;
        
            $now = Carbon::now();
            $tomorrowStart = Carbon::tomorrow()->startOfDay();
            $secondsRemaining = $now->diffInSeconds($tomorrowStart);
            $message ='';
            if (RateLimiter::tooManyAttempts($key, $limit)) {
                $locale = $request->header('Accept-Language');
                switch ($locale) {
                    case 'en':
                        $message = 'You have reached the daily limit. Please try again tomorrow.';
                        break;
                    case 'ru':
                        $message = 'Вы достигли дневного лимита. Попробуйте снова завтра.';
                        break;
                }
                throw new ThrottleRequestsException($message);
            }
            RateLimiter::hit($key, $secondsRemaining);

            $userFileId = $this->uploadImageFromFile($request->file('user_image'));
            $clothingFileId = $this->uploadImageFromFile($request->file('clothing_image'));

            $taskId = $this->createClothingSwapTask($userFileId, $clothingFileId);
            $resultUrl = $this->checkTaskStatus($taskId);

            return response()->json([
                'success' => true,
                'result_image_url' => $resultUrl
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function uploadImageFromFile($file)
    {
        $fileSize = $file->getSize();
        $fileName = $file->getClientOriginalName();
        $mimeType = $file->getMimeType();

        $response = Http::withToken($this->getValidAccessToken())
            ->post("{$this->baseUrl}/s2s/v1.1/file/cloth", [
                'files' => [
                    [
                        'content_type' => $mimeType,
                        'file_name' => $fileName,
                        'file_size' => $fileSize
                    ]
                ]
            ]);

        if (!$response->successful()) {
            throw new \Exception('Fayl metadata yüklənmədi: ' . $response->body());
        }

        $uploadData = $response->json();
        $fileId = $uploadData['result']['files'][0]['file_id'];
        $uploadUrl = $uploadData['result']['files'][0]['requests'][0]['url'];
        $uploadHeaders = $uploadData['result']['files'][0]['requests'][0]['headers'];

        Http::withHeaders($uploadHeaders)
            ->withBody(file_get_contents($file->getRealPath()), $mimeType)
            ->put($uploadUrl);

        return $fileId;
    }

    private function createClothingSwapTask($userFileId, $clothingFileId)
    {
        $response = Http::withToken($this->getValidAccessToken())
            ->post("{$this->baseUrl}/s2s/v1.0/task/cloth", [
                'request_id' => rand(1000, 9999),
                'payload' => [
                    'file_sets' => [
                        'src_ids' => [$userFileId],
                        'ref_ids' => [$clothingFileId]
                    ],
                    'actions' => [
                        [
                            'id' => 0,
                            'params' => [
                                'garment_category' => 'full_body'
                            ]
                        ]
                    ]
                ]
            ]);

        if (!$response->successful()) {
            throw new \Exception('Tapşırıq yaradılmadı: ' . $response->body());
        }

        return $response->json()['result']['task_id'];
    }
    private function checkTaskStatus($taskId)
    {
        $maxAttempts = 10;
        $attempts = 0;

        while ($attempts < $maxAttempts) {
            $response = Http::withToken($this->getValidAccessToken())
                ->get("{$this->baseUrl}/s2s/v1.0/task/cloth", [
                    'task_id' => $taskId
                ]);

            $result = $response->json()['result'] ?? [];

            if (($result['status'] ?? null) === 'success') {
                return $result['results'][0]['data'][0]['url'];
            }

            if (($result['status'] ?? null) === 'error') {
                throw new \Exception($result['error_message'] ?? 'Unknown error');
            }

            sleep(2);
            $attempts++;
        }

        throw new \Exception('Tapşırıq zaman aşımına uğradı.');
    }
    private function getValidAccessToken()
    {
            try {
                $youCam= new YouCamService;
                $token = $youCam->getAccessToken();
                return $token;
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
    }
}
