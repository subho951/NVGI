<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Psr7\Request;

class DeepSeekResumeParser
{
    protected Client $client;
    protected string $apiKey;
    protected int $maxRetries = 3;
    protected int $cacheLifetime = 60; // Cache lifetime in minutes

    /**
     * Constructor initializes the HTTP client with retry middleware
     */
    public function __construct()
    {
        // Create a handler stack with retry middleware
        $stack = HandlerStack::create();

        // Add retry middleware
        $stack->push(Middleware::retry($this->retryDecider(), $this->retryDelay()));

        // Create client with middleware stack
        $this->client = new Client([
            'base_uri' => config('deepseek.base_url'),
            'timeout'  => 30,
            'handler'  => $stack,
            'connect_timeout' => 10,
            'http_errors' => true,
        ]);

        $this->apiKey = config('deepseek.api_key');

        // Validate configuration
        $this->validateConfiguration();
    }

    /**
     * Validate required configuration exists
     *
     * @throws \RuntimeException If configuration is invalid
     */
    protected function validateConfiguration(): void
    {
        if (empty(config('deepseek.base_url'))) {
            throw new \RuntimeException('DeepSeek base URL is not configured');
        }

        if (empty($this->apiKey)) {
            throw new \RuntimeException('DeepSeek API key is not configured');
        }
    }

    /**
     * Retry decider callback for Guzzle
     *
     * @return callable
     */
    protected function retryDecider(): callable
    {
        return function (
            $retries,
            Request $request,
            ResponseInterface $response = null,
            \Exception $exception = null
        ) {
            // Don't retry if we've hit the max retries
            if ($retries >= $this->maxRetries) {
                return false;
            }

            // Retry on connection exceptions (like DNS failure)
            if ($exception instanceof ConnectException) {
                Log::warning("DeepSeek connection error, retrying ({$retries}): " . $exception->getMessage());
                return true;
            }

            // Retry on 429 (too many requests), 500, 502, 503, 504 (server errors)
            if ($response && in_array($response->getStatusCode(), [429, 500, 502, 503, 504])) {
                Log::warning("DeepSeek server error {$response->getStatusCode()}, retrying ({$retries})");
                return true;
            }

            return false;
        };
    }

    /**
     * Retry delay callback for Guzzle
     *
     * @return callable
     */
    protected function retryDelay(): callable
    {
        return function ($numberOfRetries) {
            // Exponential backoff with jitter: 2^(retries) * 100ms + random(0-100ms)
            return (int) pow(2, $numberOfRetries) * 100 + rand(0, 100);
        };
    }

    /**
     * Parse a resume file
     *
     * @param UploadedFile $file
     * @param array $options Additional options for parsing
     * @return array
     * @throws \RuntimeException
     */
    public function parseResume(UploadedFile $file, array $options = []): array
    {
        // Generate a cache key based on file contents and name
        $cacheKey = 'resume_parse_' . md5($file->getRealPath() . $file->getClientOriginalName() . filemtime($file->getRealPath()));

        // Check if we have a cached result
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // Validate file before sending
        $this->validateFile($file);

        // Merge with default options
        $parseOptions = array_merge([
            'extraction_mode' => 'advanced',
            'normalize_entities' => true,
        ], $options);

        try {
            // Ensure the file is readable
            if (!is_readable($file->getRealPath())) {
                throw new \RuntimeException("Cannot read uploaded file: {$file->getClientOriginalName()}");
            }

            $fileHandle = fopen($file->getRealPath(), 'r');
            if ($fileHandle === false) {
                throw new \RuntimeException("Failed to open file: {$file->getClientOriginalName()}");
            }

            $response = $this->client->post('/resume/parse', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ],
                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => $fileHandle,
                        'filename' => $file->getClientOriginalName(),
                    ],
                    [
                        'name' => 'options',
                        'contents' => json_encode($parseOptions),
                    ],
                ],
            ]);

            // Close the file handle
            fclose($fileHandle);

            $result = json_decode($response->getBody()->getContents(), true);

            // Cache the result
            Cache::put($cacheKey, $result, $this->cacheLifetime);

            return $result;

        } catch (GuzzleException $e) {
            Log::error('DeepSeek Resume Parsing Error', [
                'message' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
                'code' => $e->getCode(),
            ]);

            // If we have a response, include it in the log
            if (method_exists($e, 'getResponse') && $e->getResponse()) {
                Log::error('DeepSeek API Error Response: ' . $e->getResponse()->getBody()->getContents());
            }

            // Fall back to local processing if API is unavailable
            if ($e instanceof ConnectException ||
                (method_exists($e, 'getCode') && in_array($e->getCode(), [0, 6, 7, 28]))) {
                Log::info('Attempting fallback resume parsing');
                return $this->fallbackParseResume($file);
            }

            throw new \RuntimeException('Failed to parse resume: ' . $e->getMessage(), 0, $e);
        } catch (\Exception $e) {
            Log::error('Unexpected error in resume parsing', [
                'message' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
            ]);
            throw new \RuntimeException('Unexpected error while parsing resume: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Match resume data to a job description
     *
     * @param array $resumeData
     * @param string $jobDescription
     * @param array $options Additional options for matching
     * @return array
     * @throws \RuntimeException
     */
    public function matchToJobDescription(array $resumeData, string $jobDescription, array $options = []): array
    {
        // Generate a cache key based on inputs
        $cacheKey = 'resume_match_' . md5(json_encode($resumeData) . $jobDescription . json_encode($options));

        // Check if we have a cached result
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = $this->client->post('/matching/score', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => array_merge([
                    'resume_data' => $resumeData,
                    'job_description' => $jobDescription,
                ], $options),
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            // Cache the result
            Cache::put($cacheKey, $result, $this->cacheLifetime);

            return $result;

        } catch (GuzzleException $e) {
            Log::error('DeepSeek Matching Error', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            // If we have a response, include it in the log
            if (method_exists($e, 'getResponse') && $e->getResponse()) {
                Log::error('DeepSeek API Error Response: ' . $e->getResponse()->getBody()->getContents());
            }

            // Fall back to basic matching if API is unavailable
            if ($e instanceof ConnectException ||
                (method_exists($e, 'getCode') && in_array($e->getCode(), [0, 6, 7, 28]))) {
                Log::info('Attempting fallback resume matching');
                return $this->fallbackMatchResume($resumeData, $jobDescription);
            }

            throw new \RuntimeException('Failed to match resume: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Check if the DeepSeek API is available
     *
     * @return bool
     */
    public function isApiAvailable(): bool
    {
        try {
            $response = $this->client->get('/health-check', [
                'timeout' => 5,
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                ],
            ]);

            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            Log::warning('DeepSeek API health check failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate file before sending to API
     *
     * @param UploadedFile $file
     * @throws \RuntimeException
     */
    protected function validateFile(UploadedFile $file): void
    {
        // Check file type
        $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        if (!in_array($file->getMimeType(), $allowedTypes)) {
            throw new \RuntimeException('Invalid file type. Please upload a PDF or Word document.');
        }

        // Check file size (10MB max)
        $maxSize = 10 * 1024 * 1024; // 10MB
        if ($file->getSize() > $maxSize) {
            throw new \RuntimeException('File size exceeds the maximum limit of 10MB.');
        }
    }

    /**
     * Basic fallback method for resume parsing
     *
     * @param UploadedFile $file
     * @return array
     */
    protected function fallbackParseResume(UploadedFile $file): array
    {
        // Create a basic response structure with file information
        return [
            'status' => 'partial',
            'message' => 'API unavailable. Using fallback parser with limited capabilities.',
            'file_info' => [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'type' => $file->getMimeType(),
            ],
            'extraction_date' => now()->toIso8601String(),
            'metadata' => [
                'parser' => 'fallback',
                'confidence' => 'low',
            ],
            // Include basic extracted fields
            'basic_info' => [
                'file_name' => $file->getClientOriginalName(),
                // Add other basic fields that might be extractable without API
            ],
        ];
    }

    /**
     * Basic fallback method for resume matching
     *
     * @param array $resumeData
     * @param string $jobDescription
     * @return array
     */
    protected function fallbackMatchResume(array $resumeData, string $jobDescription): array
    {
        // Create a basic matching response
        return [
            'status' => 'partial',
            'message' => 'API unavailable. Using fallback matching with limited capabilities.',
            'score' => null,
            'insights' => [
                'message' => 'Unable to provide detailed match insights due to API unavailability.',
                'recommendation' => 'Please try again later when the API service is available.',
            ],
            'matching_date' => now()->toIso8601String(),
        ];
    }

    /**
     * Set cache lifetime
     *
     * @param int $minutes
     * @return self
     */
    public function setCacheLifetime(int $minutes): self
    {
        $this->cacheLifetime = $minutes;
        return $this;
    }

    /**
     * Set max retries
     *
     * @param int $retries
     * @return self
     */
    public function setMaxRetries(int $retries): self
    {
        $this->maxRetries = $retries;
        return $this;
    }
}
