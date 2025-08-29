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

    public function swapClothing(Request $request)
    {
        try {
            // Günlük limit yoxlaması
            $key = 'swap-' . auth()->guard('customers')->user()?->id;
            $limit = 10;
            $now = Carbon::now();
            $tomorrowStart = Carbon::tomorrow()->startOfDay();
            $secondsRemaining = $now->diffInSeconds($tomorrowStart);

            if (RateLimiter::tooManyAttempts($key, $limit)) {
                $locale = $request->header('Accept-Language');
                $message = match ($locale) {
                    'en' => 'You have reached the daily limit. Please try again tomorrow.',
                    'ru' => 'Вы достигли дневного лимита. Попробуйте снова завтра.',
                    default => 'Günlük limitə çatmısınız. Sabah yenidən cəhd edin.'
                };
                throw new ThrottleRequestsException($message);
            }
            RateLimiter::hit($key, $secondsRemaining);

            // Tokeni yalnız bir dəfə alırıq
            $token = $this->getValidAccessToken();

            // İki faylı birdəfəlik yüklə (yalnız 1 kredit)
            [$userFileId, $clothingFileId] = $this->uploadImagesTogether(
                $request->file('user_image'),
                $request->file('clothing_image'),
                $token
            );

            // Task yaradıb nəticəni yoxla
            $taskId = $this->createClothingSwapTask($userFileId, $clothingFileId, $token);
            $resultUrl = $this->checkTaskStatus($taskId, $token);

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

    /**
     * İki şəkli eyni API çağırışı ilə yükləyir (1 kredit)
     */
    private function uploadImagesTogether($userFile, $clothingFile, $token)
    {
        $filesMeta = [
            [
                'content_type' => $userFile->getMimeType(),
                'file_name' => $userFile->getClientOriginalName(),
                'file_size' => $userFile->getSize()
            ],
            [
                'content_type' => $clothingFile->getMimeType(),
                'file_name' => $clothingFile->getClientOriginalName(),
                'file_size' => $clothingFile->getSize()
            ]
        ];

        // 1 kredit - metadata
        $response = Http::withToken($token)
            ->post("{$this->baseUrl}/s2s/v1.1/file/cloth", [
                'files' => $filesMeta
            ]);

        if (!$response->successful()) {
            throw new \Exception('Fayl metadata yüklənmədi: ' . $response->body());
        }

        $uploadData = $response->json();
        $fileIds = [];

        foreach ($uploadData['result']['files'] as $index => $fileInfo) {
            $fileIds[] = $fileInfo['file_id'];
            $uploadUrl = $fileInfo['requests'][0]['url'];
            $uploadHeaders = $fileInfo['requests'][0]['headers'];

            // PUT yükləməsi (kredit yedirmir)
            $file = $index === 0 ? $userFile : $clothingFile;
            Http::withHeaders($uploadHeaders)
                ->withBody(file_get_contents($file->getRealPath()), $file->getMimeType())
                ->put($uploadUrl);
        }

        // Qaytar: [userFileId, clothingFileId]
        return $fileIds;
    }

    private function createClothingSwapTask($userFileId, $clothingFileId, $token)
    {
        $response = Http::withToken($token)
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

    private function checkTaskStatus($taskId, $token)
    {
        $maxAttempts = 10;
        $attempts = 0;

        while ($attempts < $maxAttempts) {
            $response = Http::withToken($token)
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
        if (cache()->has('youcam_token') && cache()->get('youcam_token_expires') > now()) {
            return cache()->get('youcam_token');
        }

        $youCam = new YouCamService;
        $tokenData = $youCam->getAccessToken();

        if (is_string($tokenData)) {
            $token = $tokenData;
            $expiresIn = 3600;
        } else {
            $token = $tokenData['access_token'] ?? null;
            $expiresIn = $tokenData['expires_in'] ?? 3600;
        }

        if (!$token) {
            throw new \Exception('Access token alınmadı');
        }

        cache()->put('youcam_token', $token, now()->addSeconds($expiresIn - 60));
        cache()->put('youcam_token_expires', now()->addSeconds($expiresIn - 60));

        return $token;
    }
}
