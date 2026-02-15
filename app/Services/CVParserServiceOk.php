<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\PdfToText\Pdf;
use PhpOffice\PhpWord\IOFactory as WordParser;
use PhpOffice\PhpWord\Element\Run;
use Exception;

class CVParserService
{
    protected $supportedFormats = ['pdf', 'doc', 'docx', 'txt', 'rtf'];

    // Improved name patterns with better accuracy
    protected $namePatterns = [
        // Full name at start of document (most common)
        '/^([A-Z][A-Z\s]{2,50})\s*$/m',  // ALL CAPS names
        '/^([A-Z][a-z]+(?:\s+[A-Z][a-z]*\.?)*\s+[A-Z][a-z]+)\s*$/m', // Proper case names
        '/^([A-Z][a-z]+(?:\s+[A-Z]\.)*\s+[A-Z][a-z]+(?:\s+[A-Z][a-z]+)*)\s*$/m', // With middle initials
    ];

    // Common CV section headers
    protected $sectionHeaders = [
        'experience' => ['work experience', 'experience', 'employment', 'professional experience', 'career history'],
        'education' => ['education', 'educational background', 'academic background', 'tertiary education'],
        'skills' => ['skills', 'technical skills', 'competencies', 'core competencies'],
        'objective' => ['career objective', 'objective', 'summary', 'professional summary'],
        'contact' => ['contact', 'contact information', 'personal information'],
        'certifications' => ['certifications', 'certificates', 'training'],
        'projects' => ['projects', 'key projects'],
        'languages' => ['languages', 'language skills'],
        'references' => ['references', 'character reference']
    ];

    // Contact patterns
    protected $contactPatterns = [
        'email' => '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/',
        'phone' => '/(?:\+?\d{1,4}[-.\s]?)?\(?\d{1,4}\)?[-.\s]?\d{1,4}[-.\s]?\d{1,4}[-.\s]?\d{1,9}/',
        'address' => '/(?:address|addr)\s*:?\s*([^\n]+)/i',
        'location' => '/(?:location|based in|from)\s*:?\s*([^\n,]+)/i'
    ];

    // Skills database for better detection
    protected $skillsKeywords = [
        'technical' => [
            'programming' => ['php', 'javascript', 'python', 'java', 'c++', 'c#', 'ruby', 'go'],
            'web' => ['html', 'css', 'react', 'vue', 'angular', 'node.js', 'laravel', 'django'],
            'database' => ['mysql', 'postgresql', 'mongodb', 'redis', 'sqlite'],
            'tools' => ['git', 'docker', 'kubernetes', 'jenkins', 'aws', 'azure'],
            'office' => ['ms office', 'excel', 'word', 'powerpoint', 'outlook']
        ],
        'soft' => [
            'communication', 'leadership', 'teamwork', 'problem solving', 'analytical',
            'time management', 'project management', 'customer service'
        ]
    ];

    public function parse(UploadedFile $file)
    {
        try {
            // Validate file
            if (!$file->isValid()) {
                throw new Exception("Invalid file upload");
            }

            $extension = strtolower($file->getClientOriginalExtension());

            if (!in_array($extension, $this->supportedFormats)) {
                throw new Exception("Unsupported file format. Please upload PDF, DOC, DOCX, TXT, or RTF files.");
            }

            // Check file size (max 10MB)
            if ($file->getSize() > 10 * 1024 * 1024) {
                throw new Exception("File size too large. Maximum allowed size is 10MB.");
            }

            // Extract text based on file type
            $text = $this->extractText($file, $extension);

            if (empty($text)) {
                throw new Exception("Could not extract text from the file. The file might be corrupted or image-based.");
            }

            // Clean the extracted text with robust encoding handling
            $cleanText = $this->cleanText($text);

            if (empty($cleanText)) {
                throw new Exception("Text extraction resulted in empty content after cleaning.");
            }

            // Parse the CV data
            $parsedData = $this->parseCV($cleanText);

            // Calculate confidence score
            $confidenceScore = $this->calculateConfidenceScore($parsedData, $cleanText);

            return [
                'success' => true,
                'message' => 'CV parsed successfully',
                'data' => array_merge($parsedData, [
                    'metadata' => [
                        'confidence_score' => $confidenceScore,
                        'parsed_at' => now()->toISOString(),
                        'file_size' => $file->getSize(),
                        'file_type' => $extension,
                        'text_length' => strlen($cleanText),
                        'original_filename' => $file->getClientOriginalName()
                    ]
                ])
            ];

        } catch (Exception $e) {
            Log::error('CV Parsing error: ' . $e->getMessage(), [
                'file' => $file->getClientOriginalName() ?? 'unknown',
                'size' => $file->getSize() ?? 0,
                'mime_type' => $file->getMimeType() ?? 'unknown',
                'error' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to parse CV: ' . $e->getMessage(),
                'data' => null,
                'debug' => [
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'original_name' => $file->getClientOriginalName()
                ]
            ];
        }
    }

    protected function extractText(UploadedFile $file, $extension)
    {
        $path = $file->getRealPath();
        $text = '';

        try {
            switch ($extension) {
                case 'pdf':
                    $text = $this->extractPdfText($path);
                    break;
                case 'doc':
                case 'docx':
                    $text = $this->extractWordText($path);
                    break;
                case 'txt':
                case 'rtf':
                    $text = $this->extractPlainText($path);
                    break;
            }
        } catch (Exception $e) {
            Log::warning("Text extraction failed for {$extension}", ['error' => $e->getMessage()]);
            throw new Exception("Could not extract text from {$extension} file: " . $e->getMessage());
        }

        if (empty(trim($text))) {
            throw new Exception("Extracted text is empty. The file might be image-based or corrupted.");
        }

        return $text;
    }

    protected function extractPdfText($path)
    {
        try {
            $text = Pdf::getText($path);

            // Additional PDF-specific encoding fixes
            $text = str_replace([
                chr(0xC2).chr(0xA0), // Non-breaking space
                chr(0xE2).chr(0x80).chr(0x93), // En dash
                chr(0xE2).chr(0x80).chr(0x94), // Em dash
            ], [' ', '-', '-'], $text);

            return $text;
        } catch (Exception $e) {
            throw new Exception("PDF text extraction failed: " . $e->getMessage());
        }
    }

    protected function extractWordText($path)
    {
        try {
            $phpWord = WordParser::load($path);
            $text = '';

            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    $elementText = $this->extractElementText($element);
                    if (!empty($elementText)) {
                        $text .= $elementText . "\n";
                    }
                }
            }

            return $text;
        } catch (Exception $e) {
            throw new Exception("Word document text extraction failed: " . $e->getMessage());
        }
    }

    protected function extractElementText($element)
    {
        $text = '';

        try {
            if (method_exists($element, 'getElements')) {
                foreach ($element->getElements() as $childElement) {
                    if ($childElement instanceof Run) {
                        $runText = $childElement->getText();
                        if (is_string($runText)) {
                            $text .= $runText . ' ';
                        }
                    } elseif (method_exists($childElement, 'getText')) {
                        $childText = $childElement->getText();
                        if (is_string($childText)) {
                            $text .= $childText . ' ';
                        }
                    }
                }
            } elseif (method_exists($element, 'getText')) {
                $elementText = $element->getText();
                if (is_string($elementText)) {
                    $text = $elementText;
                }
            }
        } catch (Exception $e) {
            // Skip problematic elements
            Log::debug('Skipped problematic element during text extraction', ['error' => $e->getMessage()]);
        }

        return trim($text);
    }

    protected function extractPlainText($path)
    {
        try {
            if (!is_readable($path)) {
                throw new Exception("File is not readable");
            }

            $text = file_get_contents($path);

            if ($text === false) {
                throw new Exception("Could not read file contents");
            }

            return $text;
        } catch (Exception $e) {
            throw new Exception("Plain text extraction failed: " . $e->getMessage());
        }
    }

    protected function cleanText($text)
    {
        try {
            // Handle various encoding issues
            $text = $this->fixEncoding($text);

            // Normalize line breaks
            $text = preg_replace('/\r\n|\r/', "\n", $text);

            // Remove control characters except tabs and newlines
            $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);

            // Remove excessive whitespace
            $text = preg_replace('/[ \t]+/', ' ', $text);
            $text = preg_replace('/\n\s*\n\s*\n+/', "\n\n", $text);

            // Fix common PDF extraction issues
            $text = preg_replace('/([a-z])([A-Z])/', '$1 $2', $text);

            // Remove any remaining malformed UTF-8 sequences
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');

            return trim($text);

        } catch (Exception $e) {
            Log::warning('Text cleaning failed, using fallback method', ['error' => $e->getMessage()]);
            return $this->fallbackTextCleaning($text);
        }
    }

    protected function fixEncoding($text)
    {
        // Detect encoding with multiple fallbacks
        $encodings = ['UTF-8', 'ISO-8859-1', 'Windows-1252', 'ASCII'];
        $detectedEncoding = null;

        foreach ($encodings as $encoding) {
            if (mb_check_encoding($text, $encoding)) {
                $detectedEncoding = $encoding;
                break;
            }
        }

        if ($detectedEncoding && $detectedEncoding !== 'UTF-8') {
            $text = mb_convert_encoding($text, 'UTF-8', $detectedEncoding);
        }

        // Handle specific problematic characters
        $replacements = [
            // Common PDF extraction issues
            "\xC2\xA0" => ' ',  // Non-breaking space
            "\xE2\x80\x93" => '-', // En dash
            "\xE2\x80\x94" => '-', // Em dash
            "\xE2\x80\x98" => "'", // Left single quotation mark
            "\xE2\x80\x99" => "'", // Right single quotation mark
            "\xE2\x80\x9C" => '"', // Left double quotation mark
            "\xE2\x80\x9D" => '"', // Right double quotation mark
            "\xE2\x80\xA2" => '•', // Bullet
            "\xEF\xBF\xBD" => '',  // Replacement character (remove)
        ];

        $text = str_replace(array_keys($replacements), array_values($replacements), $text);

        // Remove any invalid UTF-8 sequences
        $text = filter_var($text, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);

        // Final UTF-8 validation and cleaning
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        }

        return $text;
    }

    protected function fallbackTextCleaning($text)
    {
        // Very basic fallback cleaning
        $text = preg_replace('/[^\x20-\x7E\n\r\t]/', '', $text); // Keep only ASCII printable + whitespace
        $text = preg_replace('/\r\n|\r/', "\n", $text);
        $text = preg_replace('/[ \t]+/', ' ', $text);
        return trim($text);
    }

    protected function parseCV($text)
    {
        $sections = $this->identifySections($text);

        return [
            'personal_information' => $this->extractPersonalInfo($text, $sections),
            'summary' => $this->extractSummary($text, $sections),
            'experience' => $this->extractExperience($text, $sections),
            'education' => $this->extractEducation($text, $sections),
            'skills' => $this->extractSkills($text, $sections),
            'certifications' => $this->extractCertifications($text, $sections),
            'projects' => $this->extractProjects($text, $sections),
            'languages' => $this->extractLanguages($text, $sections),
            'awards' => $this->extractAwards($text, $sections),
            'references' => $this->extractReferences($text, $sections),
            'interests' => $this->extractInterests($text, $sections),
            'publications' => [],
            'social_profiles' => []
        ];
    }

    protected function identifySections($text)
    {
        $lines = explode("\n", $text);
        $sections = [];

        foreach ($lines as $index => $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Check if line is a section header
            foreach ($this->sectionHeaders as $sectionType => $headers) {
                foreach ($headers as $header) {
                    if (stripos($line, $header) !== false && strlen($line) < 50) {
                        $sections[$sectionType] = [
                            'start' => $index,
                            'header' => $line,
                            'content' => []
                        ];
                        break 2;
                    }
                }
            }
        }

        // Determine section boundaries
        $sectionTypes = array_keys($sections);
        for ($i = 0; $i < count($sectionTypes); $i++) {
            $currentType = $sectionTypes[$i];
            $startLine = $sections[$currentType]['start'];
            $endLine = isset($sectionTypes[$i + 1]) ? $sections[$sectionTypes[$i + 1]]['start'] : count($lines);

            $sections[$currentType]['content'] = array_slice($lines, $startLine + 1, $endLine - $startLine - 1);
        }

        return $sections;
    }

    protected function extractPersonalInfo($text, $sections)
    {
        $personalInfo = [
            'name' => $this->extractName($text),
            'email' => $this->extractEmail($text),
            'phone' => $this->extractPhone($text),
            'address' => $this->extractAddress($text),
            'location' => $this->extractLocation($text),
            'date_of_birth' => null,
            'nationality' => null,
            'linkedin' => $this->extractLinkedIn($text),
            'website' => $this->extractWebsite($text)
        ];

        return array_filter($personalInfo, function($value) {
            return !is_null($value) && $value !== '';
        });
    }

    protected function extractName($text)
    {
        $lines = explode("\n", $text);

        // Remove empty lines from the beginning
        $lines = array_values(array_filter($lines, function($line) {
            return !empty(trim($line));
        }));

        if (empty($lines)) {
            return null;
        }

        // Strategy 1: Check the very first line (most common for CVs)
        $firstLine = trim($lines[0]);
        if ($this->isValidName($firstLine)) {
            return $this->cleanName($firstLine);
        }

        // Strategy 2: Check first 3 lines for a valid name
        for ($i = 0; $i < min(3, count($lines)); $i++) {
            $line = trim($lines[$i]);

            // Skip obvious non-name lines
            if ($this->isNonNameLine($line)) {
                continue;
            }

            if ($this->isValidName($line)) {
                return $this->cleanName($line);
            }
        }

        // Strategy 3: Look for name patterns in first 5 lines
        for ($i = 0; $i < min(5, count($lines)); $i++) {
            $line = trim($lines[$i]);

            // Check for name in context patterns
            if (preg_match('/^(?:name\s*:?\s*)?([A-Z][A-Z\s]{8,50})$/i', $line, $matches)) {
                $candidate = trim($matches[1]);
                if ($this->isValidName($candidate)) {
                    return $this->cleanName($candidate);
                }
            }
        }

        // Strategy 4: Advanced pattern matching as fallback
        foreach ($this->namePatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $candidate = trim($matches[1]);
                if ($this->isValidName($candidate) && !$this->isNonNameLine($candidate)) {
                    return $this->cleanName($candidate);
                }
            }
        }

        return null;
    }

    protected function isNonNameLine($text)
    {
        $text = strtolower(trim($text));

        // Common non-name patterns at the start of CVs
        $nonNamePatterns = [
            'resume', 'cv', 'curriculum vitae', 'personal information',
            'contact information', 'profile', 'about me', 'summary',
            'objective', 'career objective', 'professional summary',
            'email', 'phone', 'mobile', 'address', 'location',
            'experience', 'education', 'skills', 'qualifications',
            'work experience', 'employment', 'job', 'position',
            // Job titles that might appear early
            'manager', 'director', 'coordinator', 'specialist',
            'analyst', 'consultant', 'representative', 'officer',
            'assistant', 'supervisor', 'lead', 'senior', 'junior'
        ];

        foreach ($nonNamePatterns as $pattern) {
            if (strpos($text, $pattern) !== false) {
                return true;
            }
        }

        // Check for email patterns
        if (strpos($text, '@') !== false) {
            return true;
        }

        // Check for phone patterns
        if (preg_match('/\d{3,}/', $text)) {
            return true;
        }

        // Check for address patterns
        if (preg_match('/\b(street|st|avenue|ave|road|rd|apt|apartment|suite|building)\b/i', $text)) {
            return true;
        }

        return false;
    }

    protected function isValidName($text)
    {
        if (empty($text)) {
            return false;
        }

        $text = trim($text);

        // Length check
        if (strlen($text) < 3 || strlen($text) > 80) {
            return false;
        }

        // Skip if it's obviously not a name
        if ($this->isNonNameLine($text)) {
            return false;
        }

        // Must not contain email, phone, or address indicators
        if (preg_match('/@|www\.|http|\.com|\.org|\.net|\d{3,}/', $text)) {
            return false;
        }

        // For ALL CAPS names (common in CVs)
        if (ctype_upper(str_replace([' ', '.', "'"], '', $text))) {
            $words = preg_split('/\s+/', trim($text));
            // Should have 2-5 words for a full name
            if (count($words) >= 2 && count($words) <= 5) {
                // Each word should be mostly alphabetic
                foreach ($words as $word) {
                    if (!preg_match('/^[A-Z][A-Z\'\.]*$/', $word)) {
                        return false;
                    }
                }
                return true;
            }
        }

        // For proper case names
        $words = preg_split('/\s+/', trim($text));
        if (count($words) >= 2 && count($words) <= 5) {
            foreach ($words as $word) {
                // Each word should start with capital and be mostly alphabetic
                if (!preg_match('/^[A-Z][a-z\'\.]*$/', $word) && !preg_match('/^[A-Z]\.?$/', $word)) {
                    return false;
                }
            }
            return true;
        }

        return false;
    }

    protected function cleanName($name)
    {
        if (!$name) return null;

        $name = trim($name);

        // Handle ALL CAPS names
        if (ctype_upper(str_replace([' ', '.', "'"], '', $name))) {
            // Convert to proper case
            $words = explode(' ', $name);
            $cleanWords = array_map(function($word) {
                if (strlen($word) === 1 || (strlen($word) === 2 && substr($word, -1) === '.')) {
                    return $word; // Keep initials as-is
                }
                return ucfirst(strtolower($word));
            }, $words);
            $name = implode(' ', $cleanWords);
        }

        // Clean up spacing
        $name = preg_replace('/\s+/', ' ', $name);
        $name = trim($name);

        return $name;
    }

    protected function extractEmail($text)
    {
        if (preg_match($this->contactPatterns['email'], $text, $matches)) {
            return strtolower($matches[0]);
        }
        return null;
    }

    protected function extractPhone($text)
    {
        if (preg_match($this->contactPatterns['phone'], $text, $matches)) {
            // Clean phone number
            $phone = preg_replace('/[^\d+]/', '', $matches[0]);
            if (strlen($phone) >= 7) {
                return $matches[0]; // Return original format
            }
        }
        return null;
    }

    protected function extractAddress($text)
    {
        if (preg_match($this->contactPatterns['address'], $text, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }

    protected function extractLocation($text)
    {
        // Look for location patterns
        $locationPatterns = [
            '/(?:address|location):\s*([^,\n]+(?:,\s*[^,\n]+)*)/i',
            '/([A-Z][a-z]+(?:\s+[A-Z][a-z]+)*,\s*[A-Z][a-z]+(?:\s+[A-Z][a-z]+)*)/i' // City, Country
        ];

        foreach ($locationPatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return trim($matches[1]);
            }
        }

        return null;
    }

    protected function extractLinkedIn($text)
    {
        if (preg_match('/linkedin\.com\/in\/[a-zA-Z0-9_-]+/', $text, $matches)) {
            return 'https://' . $matches[0];
        }
        return null;
    }

    protected function extractWebsite($text)
    {
        if (preg_match('/(?:https?:\/\/)?(?:www\.)?[a-zA-Z0-9-]+\.[a-zA-Z]{2,}(?:\/[^\s]*)?/', $text, $matches)) {
            $url = $matches[0];
            if (!preg_match('/^https?:\/\//', $url)) {
                $url = 'https://' . $url;
            }
            // Exclude email domains and social media
            if (!preg_match('/(gmail|yahoo|hotmail|outlook|linkedin|facebook|twitter)\.com/', $url)) {
                return $url;
            }
        }
        return null;
    }

    protected function extractSummary($text, $sections)
    {
        if (isset($sections['objective'])) {
            $content = implode("\n", $sections['objective']['content']);
            return trim($content) ?: null;
        }
        return null;
    }

    protected function extractExperience($text, $sections)
    {
        if (!isset($sections['experience'])) {
            return [];
        }

        $content = implode("\n", $sections['experience']['content']);
        $experiences = [];

        // Split by date patterns or job titles
        $blocks = preg_split('/(?=\d{2}-\d{2}-\d{4}|\d{4}\s*[-\/]\s*\d{4}|\d{4}\s*-\s*present)/i', $content);

        foreach ($blocks as $block) {
            $block = trim($block);
            if (strlen($block) < 20) continue;

            $experience = $this->parseExperienceBlock($block);
            if (!empty($experience)) {
                $experiences[] = $experience;
            }
        }

        return $experiences;
    }

    protected function parseExperienceBlock($block)
    {
        $lines = array_filter(explode("\n", $block), function($line) {
            return !empty(trim($line));
        });

        if (empty($lines)) return null;

        $experience = [
            'title' => '',
            'company' => '',
            'location' => '',
            'start_date' => '',
            'end_date' => '',
            'description' => [],
            'achievements' => []
        ];

        $dateFound = false;
        $titleFound = false;
        $companyFound = false;

        foreach ($lines as $index => $line) {
            $line = trim($line);

            // Extract dates first (priority)
            if (!$dateFound && preg_match('/(\d{2}-\d{2}-\d{4})\s*\/?\s*(\d{2}-\d{2}-\d{4}|present)/i', $line, $matches)) {
                $experience['start_date'] = $matches[1];
                $experience['end_date'] = strtolower($matches[2]) === 'present' ? 'Present' : $matches[2];
                $dateFound = true;
                continue;
            }

            // Skip date-only lines
            if (preg_match('/^\d{2}-\d{2}-\d{4}/', $line)) {
                continue;
            }

            // Extract job title (usually first non-date line in ALL CAPS or title case)
            if (!$titleFound && $this->looksLikeJobTitle($line)) {
                $experience['title'] = $line;
                $titleFound = true;
                continue;
            }

            // Extract company (usually after job title, often has location)
            if ($titleFound && !$companyFound && !preg_match('/^[•·\-\*]/', $line)) {
                // Check if this line contains location info
                if (preg_match('/^(.+?)\s+([A-Z][a-z]+,?\s*[A-Z][a-z]+)$/', $line, $matches)) {
                    $experience['company'] = trim($matches[1]);
                    $experience['location'] = trim($matches[2]);
                } else {
                    $experience['company'] = $line;
                }
                $companyFound = true;
                continue;
            }

            // Everything else is description/achievements
            if ($titleFound || $companyFound) {
                if (preg_match('/^[•·\-\*]/', $line)) {
                    $description = preg_replace('/^[•·\-\*]\s*/', '', $line);
                    $experience['description'][] = trim($description);
                }
            }
        }

        // Clean up empty values
        return array_filter($experience, function($value) {
            return !empty($value);
        });
    }

    protected function looksLikeJobTitle($line)
    {
        // Job titles are often in ALL CAPS or Title Case
        $line = trim($line);

        // Check for ALL CAPS job titles
        if (ctype_upper(str_replace([' ', '/', '-', '&'], '', $line))) {
            return true;
        }

        // Check for common job title words
        $jobTitleWords = [
            'manager', 'director', 'analyst', 'specialist', 'coordinator',
            'representative', 'agent', 'officer', 'assistant', 'supervisor',
            'lead', 'senior', 'junior', 'developer', 'engineer', 'consultant',
            'administrator', 'controller', 'executive', 'associate'
        ];

        $lineLower = strtolower($line);
        foreach ($jobTitleWords as $word) {
            if (strpos($lineLower, $word) !== false) {
                return true;
            }
        }

        return false;
    }

    protected function extractEducation($text, $sections)
    {
        if (!isset($sections['education'])) {
            return [];
        }

        $content = implode("\n", $sections['education']['content']);
        $educations = [];

        // Look for education blocks
        $lines = array_filter(explode("\n", $content), function($line) {
            return !empty(trim($line));
        });

        $currentEducation = [
            'degree' => '',
            'institution' => '',
            'year' => '',
            'location' => ''
        ];

        foreach ($lines as $line) {
            $line = trim($line);

            // Look for degree with year pattern (e.g., "Bachelor of Science in Information Technology 2017")
            if (preg_match('/(.+?)\s+(19|20)\d{2}$/', $line, $matches)) {
                if (!empty($currentEducation['degree'])) {
                    $educations[] = array_filter($currentEducation);
                }
                $currentEducation = [
                    'degree' => trim($matches[1]),
                    'institution' => '',
                    'year' => $matches[2] . substr($matches[0], -2), // Full year
                    'location' => ''
                ];
            }
            // Look for standalone degree patterns
            elseif (preg_match('/\b(bachelor|master|phd|doctorate|diploma|certificate|degree|bsc|msc|mba|ba|bs|ma|md|jd|btech|mtech|bca|mca|be|me)\b/i', $line)) {
                if (empty($currentEducation['degree'])) {
                    $currentEducation['degree'] = $line;
                }
            }
            // Look for institutions
            elseif (preg_match('/\b(university|college|institute|school|academy|polytechnic)\b/i', $line)) {
                if (empty($currentEducation['institution'])) {
                    $currentEducation['institution'] = $line;
                }
            }
            // Look for standalone years
            elseif (preg_match('/\b(19|20)\d{2}\b/', $line, $matches)) {
                if (empty($currentEducation['year'])) {
                    $currentEducation['year'] = $matches[0];
                }
            }
            // Look for locations (City, Country pattern)
            elseif (preg_match('/^[A-Z][a-z]+(?:\s+[A-Z][a-z]+)*,\s*[A-Z][a-z]+/', $line)) {
                if (empty($currentEducation['location'])) {
                    $currentEducation['location'] = $line;
                }
            }
        }

        // Add the last education entry
        if (!empty($currentEducation['degree']) || !empty($currentEducation['institution'])) {
            $educations[] = array_filter($currentEducation);
        }

        return $educations;
    }

    protected function extractSkills($text, $sections)
    {
        $skills = [];

        // Look in skills section first
        if (isset($sections['skills'])) {
            $content = implode("\n", $sections['skills']['content']);
            $skills = array_merge($skills, $this->parseSkillsFromContent($content));
        }

        // Also scan entire document for technical skills
        $skills = array_merge($skills, $this->scanForTechnicalSkills($text));

        // Remove duplicates and categorize
        return $this->categorizeSkills(array_unique($skills));
    }

    protected function parseSkillsFromContent($content)
    {
        $skills = [];
        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Split by common separators
            $items = preg_split('/[,;|•·\-]/', $line);
            foreach ($items as $item) {
                $skill = trim($item);
                if (strlen($skill) > 1 && strlen($skill) < 50) {
                    $skills[] = $skill;
                }
            }
        }

        return $skills;
    }

    protected function scanForTechnicalSkills($text)
    {
        $foundSkills = [];
        $text = strtolower($text);

        foreach ($this->skillsKeywords['technical'] as $category => $skills) {
            foreach ($skills as $skill) {
                if (strpos($text, strtolower($skill)) !== false) {
                    $foundSkills[] = $skill;
                }
            }
        }

        return $foundSkills;
    }

    protected function categorizeSkills($skills)
    {
        $categorized = [
            'technical' => [],
            'soft' => [],
            'general' => []
        ];

        foreach ($skills as $skill) {
            $skillLower = strtolower($skill);
            $category = 'general';

            // Check technical skills
            foreach ($this->skillsKeywords['technical'] as $techCategory => $techSkills) {
                foreach ($techSkills as $techSkill) {
                    if (stripos($skill, $techSkill) !== false) {
                        $category = 'technical';
                        break 2;
                    }
                }
            }

            // Check soft skills
            if ($category === 'general') {
                foreach ($this->skillsKeywords['soft'] as $softSkill) {
                    if (stripos($skill, $softSkill) !== false) {
                        $category = 'soft';
                        break;
                    }
                }
            }

            $categorized[$category][] = $skill;
        }

        // Remove empty categories and return as array
        return array_filter($categorized, function($categorySkills) {
            return !empty($categorySkills);
        });
    }

    protected function extractCertifications($text, $sections)
    {
        if (!isset($sections['certifications'])) {
            return [];
        }

        $content = implode("\n", $sections['certifications']['content']);
        $certifications = [];

        $lines = array_filter(explode("\n", $content), function($line) {
            return !empty(trim($line));
        });

        foreach ($lines as $line) {
            $line = trim($line);
            if (strlen($line) > 5) {
                $certifications[] = ['name' => $line];
            }
        }

        return $certifications;
    }

    protected function extractProjects($text, $sections)
    {
        if (!isset($sections['projects'])) {
            return [];
        }

        // Basic implementation
        return [];
    }

    protected function extractLanguages($text, $sections)
    {
        if (!isset($sections['languages'])) {
            return [];
        }

        // Basic implementation
        return [];
    }

    protected function extractAwards($text, $sections)
    {
        return [];
    }

    protected function extractReferences($text, $sections)
    {
        if (!isset($sections['references'])) {
            return [];
        }

        // Basic implementation
        return [];
    }

    protected function extractInterests($text, $sections)
    {
        return [];
    }

    protected function calculateConfidenceScore($data, $text)
    {
        $score = 0;
        $maxScore = 100;

        // Name extraction (25 points)
        if (!empty($data['personal_information']['name'])) {
            $score += 25;
        }

        // Contact info (25 points)
        if (!empty($data['personal_information']['email'])) {
            $score += 15;
        }
        if (!empty($data['personal_information']['phone'])) {
            $score += 10;
        }

        // Experience (25 points)
        if (!empty($data['experience'])) {
            $score += min(count($data['experience']) * 8, 25);
        }

        // Education (15 points)
        if (!empty($data['education'])) {
            $score += min(count($data['education']) * 8, 15);
        }

        // Skills (10 points)
        if (!empty($data['skills'])) {
            $totalSkills = 0;
            foreach ($data['skills'] as $category => $skills) {
                $totalSkills += count($skills);
            }
            $score += min($totalSkills * 2, 10);
        }

        return round(min($score, $maxScore), 2);
    }
}
