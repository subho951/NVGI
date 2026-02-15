<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\PdfToText\Pdf;
use PhpOffice\PhpWord\IOFactory as WordParser;
use PhpOffice\PhpWord\Element\Run;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextRun;
use Exception;
use Carbon\Carbon;

class CVParserService
{
    protected $supportedFormats = ['pdf', 'doc', 'docx', 'txt', 'rtf'];
    protected $uploadPath = 'uploads/cvs/';
    protected $maxFileSize = 15 * 1024 * 1024; // 15MB

    // Enhanced name patterns for better accuracy
    protected $namePatterns = [
        // Full name patterns with better context awareness
        '/^([A-Z][A-Z\s\.]{3,50})\s*$/m',  // ALL CAPS names (common in CVs)
        '/^([A-Z][a-z]+(?:\s+[A-Z][a-z]*\.?)*\s+[A-Z][a-z]+(?:\s+[A-Z][a-z]+)*)\s*$/m', // Proper case full names
        '/^([A-Z][a-z]+(?:\s+[A-Z]\.)*\s+[A-Z][a-z]+)\s*$/m', // Name with middle initials
        '/^((?:Dr|Mr|Ms|Mrs)\.?\s+[A-Z][a-z]+(?:\s+[A-Z][a-z]+)+)$/m', // With titles
        '/Name\s*:?\s*([A-Z][A-Za-z\s\.]{3,50})/im', // Explicit name field
        '/^([A-Z][a-z]+\s+[A-Z][a-z]+(?:\s+[A-Z][a-z]+)*)\s*$/m' // Simple first last name
    ];

    // Enhanced section detection patterns
    protected $sectionPatterns = [
        'personal_info' => [
            'personal information', 'personal details', 'personal profile', 'contact',
            'contact information', 'contact details', 'profile', 'about'
        ],
        'objective' => [
            'career objective', 'objective', 'career goal', 'professional summary',
            'summary', 'profile summary', 'about me', 'overview'
        ],
        'experience' => [
            'work experience', 'professional experience', 'employment history',
            'experience', 'career history', 'work history', 'employment'
        ],
        'education' => [
            'education', 'educational background', 'academic background',
            'qualifications', 'educational qualifications', 'academic qualifications'
        ],
        'skills' => [
            'skills', 'technical skills', 'key skills', 'core competencies',
            'competencies', 'expertise', 'proficiency', 'capabilities'
        ],
        'languages' => [
            'languages', 'language skills', 'languages known', 'linguistic skills'
        ],
        'certifications' => [
            'certifications', 'certificates', 'training', 'professional training',
            'courses', 'additional certificates'
        ]
    ];

    // Enhanced contact patterns
    protected $contactPatterns = [
        'email' => [
            '/\b([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})\b/',
            '/(?:email|e-mail|mail)\s*(?:id|address)?\s*:?\s*([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/i'
        ],
        'phone' => [
            '/(?:mobile|cell|phone|contact|tel)\s*(?:no\.?|number)?\s*:?\s*(\+?[\d\s\-\(\)\.]{8,20})/i',
            '/(\+971\s*\d{1,3}\s*\d{6,7})/i', // UAE format
            '/(\+91\s*\d{10})/i', // India format
            '/(\+\d{1,4}[-.\s]?\d{2,4}[-.\s]?\d{6,12})/i', // International
            '/\b(\d{10,15})\b/', // Simple digit sequences
            '/(\d{3,4}[-.\s]?\d{6,8})/' // Local formats
        ],
        'location' => [
            '/(?:address|location|based\s+in|residence)\s*:?\s*([^\n]+)/i',
            '/([A-Z][a-z]+(?:\s+[A-Z][a-z]+)*,\s*[A-Z][a-z]+(?:\s+[A-Z][a-z]+)*)/i'
        ]
    ];

    // Enhanced skills database
    protected $skillsDatabase = [
        'programming' => [
            'php', 'javascript', 'python', 'java', 'c++', 'c#', 'ruby', 'swift',
            'kotlin', 'typescript', 'dart', 'go', 'rust', 'scala', 'perl'
        ],
        'web_technologies' => [
            'html', 'css', 'react', 'vue.js', 'angular', 'node.js', 'laravel',
            'bootstrap', 'jquery', 'express.js', 'django', 'flask', 'symfony'
        ],
        'databases' => [
            'mysql', 'postgresql', 'mongodb', 'redis', 'oracle', 'sql server',
            'sqlite', 'firebase', 'elasticsearch'
        ],
        'office_tools' => [
            'ms office', 'excel', 'word', 'powerpoint', 'outlook', 'access',
            'google workspace', 'sheets', 'docs', 'slides'
        ],
        'design_tools' => [
            'photoshop', 'illustrator', 'figma', 'sketch', 'canva', 'autocad',
            'adobe xd', 'indesign', 'premiere pro', 'after effects'
        ],
        'soft_skills' => [
            'communication', 'leadership', 'teamwork', 'problem solving',
            'time management', 'customer service', 'negotiation', 'training',
            'project management', 'analytical thinking'
        ]
    ];

    // Common languages for language detection
    protected $languages = [
        'english', 'arabic', 'hindi', 'urdu', 'tamil', 'malayalam', 'bengali',
        'french', 'spanish', 'german', 'italian', 'portuguese', 'russian',
        'chinese', 'japanese', 'korean', 'punjabi', 'gujarati', 'marathi',
        'sindhi', 'telugu', 'kannada', 'persian', 'turkish', 'dutch'
    ];

    /**
     * Main parsing method with enhanced accuracy
     */
    public function parse(UploadedFile $file, array $options = [])
    {
        try {
            $this->validateFile($file);

            $extension = strtolower($file->getClientOriginalExtension());
            $storedPath = $this->storeFile($file);

            // Extract text with enhanced methods
            $text = $this->extractTextAdvanced($file, $extension);

            if (empty($text)) {
                throw new Exception("Could not extract text from the file.");
            }

            // Clean and preprocess text
            $cleanText = $this->cleanText($text);

            // Parse CV with enhanced accuracy
            $parsedData = $this->parseCV($cleanText);

            // Calculate confidence score
            $confidenceScore = $this->calculateConfidenceScore($parsedData, $cleanText);

            $result = [
                'success' => true,
                'message' => 'CV parsed successfully',
                'data' => array_merge($parsedData, [
                    'metadata' => [
                        'confidence_score' => $confidenceScore,
                        'parsed_at' => Carbon::now()->toISOString(),
                        'file_size' => $file->getSize(),
                        'file_type' => $extension,
                        'text_length' => strlen($cleanText),
                        'original_filename' => $file->getClientOriginalName(),
                        'stored_path' => $storedPath
                    ]
                ])
            ];

            Log::info('CV parsing completed successfully', [
                'filename' => $file->getClientOriginalName(),
                'confidence_score' => $confidenceScore
            ]);

            return $result;

        } catch (Exception $e) {
            Log::error('CV Parsing error: ' . $e->getMessage(), [
                'file' => $file->getClientOriginalName() ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to parse CV: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Enhanced file validation
     */
    protected function validateFile(UploadedFile $file)
    {
        if (!$file->isValid()) {
            throw new Exception("Invalid file upload");
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $this->supportedFormats)) {
            throw new Exception("Unsupported file format. Supported formats: " . implode(', ', $this->supportedFormats));
        }

        if ($file->getSize() > $this->maxFileSize) {
            throw new Exception("File size too large. Maximum allowed size is " . ($this->maxFileSize / 1024 / 1024) . "MB");
        }
    }

    /**
     * Store file securely
     */
    protected function storeFile(UploadedFile $file)
    {
        $fileName = Str::uuid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($this->uploadPath, $fileName, 'local');

        if (!$path) {
            throw new Exception("Failed to store uploaded file");
        }

        return $path;
    }

    /**
     * Enhanced text extraction
     */
    protected function extractTextAdvanced(UploadedFile $file, $extension)
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
            throw new Exception("Text extraction failed: " . $e->getMessage());
        }

        if (empty(trim($text))) {
            throw new Exception("No readable text found in the file");
        }

        return $text;
    }

    /**
     * Extract PDF text
     */
    protected function extractPdfText($path)
    {
        if (!class_exists('Spatie\PdfToText\Pdf')) {
            throw new Exception("PDF parser not available");
        }

        try {
            $text = Pdf::getText($path);
            return $this->fixPdfEncoding($text);
        } catch (Exception $e) {
            throw new Exception("PDF text extraction failed: " . $e->getMessage());
        }
    }

    /**
     * Fix PDF encoding issues
     */
    protected function fixPdfEncoding($text)
    {
        // Fix common PDF encoding issues
        $replacements = [
            chr(0xC2).chr(0xA0) => ' ', // Non-breaking space
            chr(0xE2).chr(0x80).chr(0x93) => '-', // En dash
            chr(0xE2).chr(0x80).chr(0x94) => '-', // Em dash
            chr(0xE2).chr(0x80).chr(0x99) => "'", // Right single quotation
            chr(0xE2).chr(0x80).chr(0x9D) => '"', // Right double quotation
            chr(0xE2).chr(0x80).chr(0xA2) => '•', // Bullet
        ];

        $text = str_replace(array_keys($replacements), array_values($replacements), $text);

        // Fix word spacing
        $text = preg_replace('/([a-z])([A-Z])/', '$1 $2', $text);

        return $text;
    }

    /**
     * Extract Word document text
     */
    protected function extractWordText($path)
    {
        if (!class_exists('PhpOffice\PhpWord\IOFactory')) {
            throw new Exception("Word parser not available");
        }

        try {
            $phpWord = WordParser::load($path);
            $text = '';

            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    $text .= $this->extractElementText($element) . "\n";
                }
            }

            return $text;
        } catch (Exception $e) {
            throw new Exception("Word text extraction failed: " . $e->getMessage());
        }
    }

    /**
     * Extract text from Word elements
     */
    protected function extractElementText($element)
    {
        $text = '';

        try {
            if ($element instanceof TextRun) {
                foreach ($element->getElements() as $textElement) {
                    if ($textElement instanceof Text) {
                        $text .= $textElement->getText() . ' ';
                    }
                }
            } elseif ($element instanceof Run) {
                $runText = $element->getText();
                if (is_string($runText)) {
                    $text .= $runText . ' ';
                }
            } elseif (method_exists($element, 'getElements')) {
                foreach ($element->getElements() as $childElement) {
                    $text .= $this->extractElementText($childElement);
                }
            } elseif (method_exists($element, 'getText')) {
                $elementText = $element->getText();
                if (is_string($elementText)) {
                    $text .= $elementText . ' ';
                }
            }
        } catch (Exception $e) {
            // Skip problematic elements
        }

        return trim($text);
    }

    /**
     * Extract plain text
     */
    protected function extractPlainText($path)
    {
        if (!is_readable($path)) {
            throw new Exception("File is not readable");
        }

        $text = file_get_contents($path);

        if ($text === false) {
            throw new Exception("Could not read file contents");
        }

        return $this->convertEncoding($text);
    }

    /**
     * Convert text encoding
     */
    protected function convertEncoding($text)
    {
        $encodings = ['UTF-8', 'UTF-16', 'ISO-8859-1', 'Windows-1252', 'ASCII'];
        $detectedEncoding = mb_detect_encoding($text, $encodings, true);

        if ($detectedEncoding && $detectedEncoding !== 'UTF-8') {
            $text = mb_convert_encoding($text, 'UTF-8', $detectedEncoding);
        }

        // Remove BOM
        $text = preg_replace('/^\xEF\xBB\xBF/', '', $text);

        return $text;
    }

    /**
     * Clean extracted text
     */
    protected function cleanText($text)
    {
        // Normalize line endings
        $text = preg_replace('/\r\n|\r/', "\n", $text);

        // Remove control characters
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);

        // Fix spacing
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n\s*\n\s*\n+/', "\n\n", $text);

        // Fix bullet points
        $text = str_replace(['•', '▪', '▫', '◦', '‣'], '•', $text);

        return trim($text);
    }

    /**
     * Main CV parsing method
     */
    protected function parseCV($text)
    {
        $sections = $this->identifySections($text);

        return [
            'personal_information' => $this->extractPersonalInfo($text, $sections),
            'objective' => $this->extractObjective($text, $sections),
            'experience' => $this->extractExperience($text, $sections),
            'education' => $this->extractEducation($text, $sections),
            'skills' => $this->extractSkills($text, $sections),
            'languages' => $this->extractLanguages($text, $sections),
            'certifications' => $this->extractCertifications($text, $sections)
        ];
    }

    /**
     * Identify sections in CV
     */
    protected function identifySections($text)
    {
        $lines = explode("\n", $text);
        $sections = [];

        foreach ($lines as $index => $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $sectionType = $this->detectSectionType($line);
            if ($sectionType) {
                $sections[$sectionType] = [
                    'start' => $index,
                    'header' => $line,
                    'content' => []
                ];
            }
        }

        // Determine section boundaries
        $this->setSectionBoundaries($sections, $lines);

        return $sections;
    }

    /**
     * Detect section type
     */
    protected function detectSectionType($line)
    {
        $lineFormatted = strtolower(preg_replace('/[^a-z\s]/', '', $line));

        foreach ($this->sectionPatterns as $sectionType => $patterns) {
            foreach ($patterns as $pattern) {
                if (strpos($lineFormatted, $pattern) !== false) {
                    return $sectionType;
                }
            }
        }

        return null;
    }

    /**
     * Set section boundaries
     */
    protected function setSectionBoundaries($sections, $lines)
    {
        $sectionTypes = array_keys($sections);

        for ($i = 0; $i < count($sectionTypes); $i++) {
            $currentType = $sectionTypes[$i];
            $startLine = $sections[$currentType]['start'];
            $endLine = isset($sectionTypes[$i + 1]) ? $sections[$sectionTypes[$i + 1]]['start'] : count($lines);

            $contentLines = [];
            for ($j = $startLine + 1; $j < $endLine; $j++) {
                $contentLine = trim($lines[$j] ?? '');
                if (!empty($contentLine)) {
                    $contentLines[] = $contentLine;
                }
            }

            $sections[$currentType]['content'] = $contentLines;
        }
    }

    /**
     * Extract personal information
     */
    protected function extractPersonalInfo($text, $sections)
    {
        $personalInfo = [
            'name' => $this->extractName($text),
            'email' => $this->extractEmail($text),
            'phone' => $this->extractPhone($text),
            'location' => $this->extractLocation($text)
        ];

        return array_filter($personalInfo, function($value) {
            return !empty($value);
        });
    }

    /**
     * Extract name with enhanced accuracy
     */
    protected function extractName($text)
    {
        $lines = explode("\n", $text);
        $lines = array_values(array_filter($lines, function($line) {
            return !empty(trim($line));
        }));

        if (empty($lines)) return null;

        // Check first few lines for name patterns
        for ($i = 0; $i < min(5, count($lines)); $i++) {
            $line = trim($lines[$i]);

            // Skip lines that are clearly not names
            if ($this->isNotName($line)) {
                continue;
            }

            // Check if line looks like a name
            if ($this->looksLikeName($line)) {
                return $this->cleanName($line);
            }
        }

        // Try pattern matching
        foreach ($this->namePatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $candidate = trim($matches[1]);
                if ($this->isValidName($candidate)) {
                    return $this->cleanName($candidate);
                }
            }
        }

        return null;
    }

    /**
     * Check if line is not a name
     */
    protected function isNotName($line)
    {
        $indicators = [
            '@', 'www.', 'http', '.com', '.org', '.net',
            'phone', 'email', 'address', 'cv', 'resume'
        ];

        $lowerLine = strtolower($line);
        foreach ($indicators as $indicator) {
            if (strpos($lowerLine, $indicator) !== false) {
                return true;
            }
        }

        return preg_match('/\d{3,}/', $line) || strlen($line) > 60;
    }

    /**
     * Check if line looks like a name
     */
    protected function looksLikeName($line)
    {
        // Check for proper name format
        if (preg_match('/^[A-Z][A-Z\s\.]{2,50}$/', $line)) {
            return true; // ALL CAPS name
        }

        if (preg_match('/^[A-Z][a-z]+(?:\s+[A-Z][a-z]*\.?)*\s+[A-Z][a-z]+/', $line)) {
            return true; // Proper case name
        }

        return false;
    }

    /**
     * Validate name candidate
     */
    protected function isValidName($name)
    {
        if (empty($name) || strlen($name) < 3 || strlen($name) > 60) {
            return false;
        }

        return !$this->isNotName($name);
    }

    /**
     * Clean name
     */
    protected function cleanName($name)
    {
        $name = trim($name);

        // Convert ALL CAPS to proper case
        if (ctype_upper(str_replace([' ', '.', "'"], '', $name))) {
            $words = explode(' ', $name);
            $name = implode(' ', array_map('ucfirst', array_map('strtolower', $words)));
        }

        return $name;
    }

    /**
     * Extract email
     */
    protected function extractEmail($text)
    {
        foreach ($this->contactPatterns['email'] as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $email = strtolower(trim($matches[1]));
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    return $email;
                }
            }
        }

        return null;
    }

    /**
     * Extract phone number
     */
    protected function extractPhone($text)
    {
        foreach ($this->contactPatterns['phone'] as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $phone = $matches[1] ?? $matches[0];
                $cleanPhone = preg_replace('/[^\d+]/', '', $phone);

                if (strlen($cleanPhone) >= 7 && strlen($cleanPhone) <= 15) {
                    return trim($phone);
                }
            }
        }

        return null;
    }

    /**
     * Extract location
     */
    protected function extractLocation($text)
    {
        foreach ($this->contactPatterns['location'] as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $location = trim($matches[1]);
                if (strlen($location) > 3 && strlen($location) < 100) {
                    return $location;
                }
            }
        }

        return null;
    }

    /**
     * Extract objective/summary
     */
    protected function extractObjective($text, $sections)
    {
        if (isset($sections['objective'])) {
            $content = implode(" ", $sections['objective']['content']);
            return !empty($content) ? $content : null;
        }

        return null;
    }

    /**
     * Extract work experience
     */
    protected function extractExperience($text, $sections)
    {
        if (!isset($sections['experience'])) {
            return [];
        }

        $content = implode("\n", $sections['experience']['content']);
        return $this->parseExperience($content);
    }

    /**
     * Parse experience section
     */
    protected function parseExperience($content)
    {
        $experiences = [];
        $lines = explode("\n", $content);
        $currentExperience = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Check if this is a new job entry
            if ($this->isNewJobEntry($line)) {
                if ($currentExperience && !empty($currentExperience['designation'])) {
                    $experiences[] = $currentExperience;
                }

                $currentExperience = [
                    'designation' => '',
                    'company' => '',
                    'location' => '',
                    'duration' => '',
                    'description' => []
                ];
            }

            // Parse job details
            if ($currentExperience) {
                $this->parseJobLine($line, $currentExperience);
            }
        }

        if ($currentExperience && !empty($currentExperience['designation'])) {
            $experiences[] = $currentExperience;
        }

        return $experiences;
    }

    /**
     * Check if line is a new job entry
     */
    protected function isNewJobEntry($line)
    {
        // Check for company indicators
        $companyIndicators = [
            'ltd', 'llc', 'inc', 'corp', 'company', 'pvt', 'private', 'limited'
        ];

        $lowerLine = strtolower($line);
        foreach ($companyIndicators as $indicator) {
            if (strpos($lowerLine, $indicator) !== false) {
                return true;
            }
        }

        // Check for job title patterns
        $jobTitles = [
            'manager', 'executive', 'analyst', 'specialist', 'coordinator',
            'assistant', 'supervisor', 'lead', 'senior', 'developer', 'engineer'
        ];

        foreach ($jobTitles as $title) {
            if (strpos($lowerLine, $title) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Parse job line details
     */
    protected function parseJobLine($line, &$experience)
    {
        // Extract dates
        if (preg_match('/(\d{4})\s*[-–]\s*(\d{4}|present|current)/i', $line, $matches)) {
            $experience['duration'] = $matches[0];
            return;
        }

        // Extract designation patterns
        if (preg_match('/(?:designation|position|role)\s*:?\s*(.+)/i', $line, $matches)) {
            $experience['designation'] = trim($matches[1]);
            return;
        }

        // Extract company patterns
        if (preg_match('/(?:organization|company)\s*:?\s*(.+)/i', $line, $matches)) {
            $experience['company'] = trim($matches[1]);
            return;
        }

        // If no designation yet and line looks like a job title
        if (empty($experience['designation']) && $this->looksLikeJobTitle($line)) {
            $experience['designation'] = $line;
            return;
        }

        // If no company yet and line looks like a company
        if (empty($experience['company']) && $this->looksLikeCompany($line)) {
            $experience['company'] = $line;
            return;
        }

        // Otherwise, it's probably a description
        if (strlen($line) > 20) {
            $experience['description'][] = $line;
        }
    }

    /**
     * Check if line looks like a job title
     */
    protected function looksLikeJobTitle($line)
    {
        $jobTitles = [
            'manager', 'executive', 'analyst', 'specialist', 'coordinator',
            'assistant', 'supervisor', 'lead', 'senior', 'developer', 'engineer',
            'representative', 'officer', 'consultant', 'administrator'
        ];

        $lowerLine = strtolower($line);
        foreach ($jobTitles as $title) {
            if (strpos($lowerLine, $title) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if line looks like a company
     */
    protected function looksLikeCompany($line)
    {
        $companyIndicators = [
            'ltd', 'llc', 'inc', 'corp', 'company', 'pvt', 'private', 'limited',
            'solutions', 'technologies', 'services', 'group', 'systems'
        ];

        $lowerLine = strtolower($line);
        foreach ($companyIndicators as $indicator) {
            if (strpos($lowerLine, $indicator) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extract education
     */
    protected function extractEducation($text, $sections)
    {
        if (!isset($sections['education'])) {
            return [];
        }

        $content = implode("\n", $sections['education']['content']);
        return $this->parseEducation($content);
    }

    /**
     * Parse education section
     */
    protected function parseEducation($content)
    {
        $educations = [];
        $lines = explode("\n", $content);
        $currentEducation = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Check if this is a degree line
            if ($this->isDegreeOrQualification($line)) {
                if ($currentEducation && !empty($currentEducation['degree'])) {
                    $educations[] = $currentEducation;
                }

                $currentEducation = [
                    'degree' => $line,
                    'institution' => '',
                    'year' => '',
                    'location' => ''
                ];
            } elseif ($currentEducation) {
                // Extract year
                if (preg_match('/\b(19|20)\d{2}\b/', $line, $matches)) {
                    if (empty($currentEducation['year'])) {
                        $currentEducation['year'] = $matches[0];
                    }
                }

                // Extract institution
                if ($this->looksLikeInstitution($line) && empty($currentEducation['institution'])) {
                    $currentEducation['institution'] = $line;
                }
            }
        }

        if ($currentEducation && !empty($currentEducation['degree'])) {
            $educations[] = $currentEducation;
        }

        return $educations;
    }

    /**
     * Check if line is a degree or qualification
     */
    protected function isDegreeOrQualification($line)
    {
        $degrees = [
            'bachelor', 'master', 'phd', 'doctorate', 'diploma', 'certificate',
            'bsc', 'msc', 'mba', 'ba', 'bs', 'ma', 'md', 'btech', 'mtech',
            'bca', 'mca', 'be', 'me', 'bcom', 'mcom', 'llb', 'llm',
            'higher secondary', 'secondary', 'intermediate', 'ssc', 'hsc'
        ];

        $lowerLine = strtolower($line);
        foreach ($degrees as $degree) {
            if (strpos($lowerLine, $degree) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if line looks like an institution
     */
    protected function looksLikeInstitution($line)
    {
        $institutionWords = [
            'university', 'college', 'institute', 'school', 'academy',
            'polytechnic', 'board', 'education'
        ];

        $lowerLine = strtolower($line);
        foreach ($institutionWords as $word) {
            if (strpos($lowerLine, $word) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extract skills
     */
    protected function extractSkills($text, $sections)
    {
        $skills = [];

        // Extract from skills section
        if (isset($sections['skills'])) {
            $content = implode("\n", $sections['skills']['content']);
            $skills = array_merge($skills, $this->parseSkills($content));
        }

        // Extract from entire text
        $skills = array_merge($skills, $this->scanForSkills($text));

        // Remove duplicates and categorize
        $skills = array_unique($skills);
        return $this->categorizeSkills($skills);
    }

    /**
     * Parse skills from content
     */
    protected function parseSkills($content)
    {
        $skills = [];
        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Split by common separators
            $items = preg_split('/[,;|•·\-\n]/', $line);
            foreach ($items as $item) {
                $skill = trim($item);
                if (strlen($skill) > 1 && strlen($skill) < 50) {
                    $skills[] = $this->normalizeSkill($skill);
                }
            }
        }

        return $skills;
    }

    /**
     * Scan entire text for skills
     */
    protected function scanForSkills($text)
    {
        $foundSkills = [];
        $textLower = strtolower($text);

        foreach ($this->skillsDatabase as $category => $skillsList) {
            foreach ($skillsList as $skill) {
                if (preg_match('/\b' . preg_quote(strtolower($skill), '/') . '\b/', $textLower)) {
                    $foundSkills[] = $skill;
                }
            }
        }

        return $foundSkills;
    }

    /**
     * Normalize skill name
     */
    protected function normalizeSkill($skill)
    {
        $skill = trim($skill);
        $skill = preg_replace('/[^\w\s\+\#\.]/', '', $skill);
        return $skill;
    }

    /**
     * Categorize skills
     */
    protected function categorizeSkills($skills)
    {
        $categorized = [];

        foreach ($this->skillsDatabase as $category => $categorySkills) {
            $matchedSkills = [];

            foreach ($skills as $skill) {
                foreach ($categorySkills as $categorySkill) {
                    if (stripos($skill, $categorySkill) !== false ||
                        stripos($categorySkill, $skill) !== false) {
                        if (!in_array($skill, $matchedSkills)) {
                            $matchedSkills[] = $skill;
                        }
                    }
                }
            }

            if (!empty($matchedSkills)) {
                $categorized[$category] = $matchedSkills;
            }
        }

        // Add uncategorized skills
        $allCategorizedSkills = [];
        foreach ($categorized as $catSkills) {
            $allCategorizedSkills = array_merge($allCategorizedSkills, $catSkills);
        }

        $uncategorized = array_diff($skills, $allCategorizedSkills);
        if (!empty($uncategorized)) {
            $categorized['other'] = $uncategorized;
        }

        return $categorized;
    }

    /**
     * Extract languages
     */
    protected function extractLanguages($text, $sections)
    {
        $languages = [];

        // Extract from languages section
        if (isset($sections['languages'])) {
            $content = implode("\n", $sections['languages']['content']);
            $languages = array_merge($languages, $this->parseLanguages($content));
        }

        // Scan entire text for language mentions
        $languages = array_merge($languages, $this->scanForLanguages($text));

        return array_values(array_unique($languages));
    }

    /**
     * Parse languages from content
     */
    protected function parseLanguages($content)
    {
        $foundLanguages = [];
        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            $line = strtolower(trim($line));

            foreach ($this->languages as $language) {
                if (strpos($line, $language) !== false) {
                    $foundLanguages[] = ucfirst($language);
                }
            }
        }

        return $foundLanguages;
    }

    /**
     * Scan text for languages
     */
    protected function scanForLanguages($text)
    {
        $foundLanguages = [];
        $textLower = strtolower($text);

        foreach ($this->languages as $language) {
            if (preg_match('/\b' . preg_quote($language, '/') . '\b/', $textLower)) {
                $foundLanguages[] = ucfirst($language);
            }
        }

        return $foundLanguages;
    }

    /**
     * Extract certifications
     */
    protected function extractCertifications($text, $sections)
    {
        $certifications = [];

        if (isset($sections['certifications'])) {
            $content = implode("\n", $sections['certifications']['content']);
            $lines = explode("\n", $content);

            foreach ($lines as $line) {
                $line = trim($line);
                if (strlen($line) > 10 && strlen($line) < 200) {
                    // Remove bullet points
                    $line = preg_replace('/^[•·\-\*\s➢]*/', '', $line);
                    $certifications[] = trim($line);
                }
            }
        }

        return array_filter($certifications);
    }

    /**
     * Calculate confidence score
     */
    protected function calculateConfidenceScore($data, $text)
    {
        $score = 0;
        $maxScore = 100;

        // Personal information (30 points)
        $personalInfo = $data['personal_information'] ?? [];
        if (!empty($personalInfo['name'])) $score += 10;
        if (!empty($personalInfo['email'])) $score += 8;
        if (!empty($personalInfo['phone'])) $score += 7;
        if (!empty($personalInfo['location'])) $score += 5;

        // Experience (25 points)
        $experience = $data['experience'] ?? [];
        if (!empty($experience)) {
            $score += min(count($experience) * 8, 25);
        }

        // Education (20 points)
        $education = $data['education'] ?? [];
        if (!empty($education)) {
            $score += min(count($education) * 10, 20);
        }

        // Skills (15 points)
        $skills = $data['skills'] ?? [];
        if (!empty($skills)) {
            $totalSkills = 0;
            foreach ($skills as $category => $skillList) {
                if (is_array($skillList)) {
                    $totalSkills += count($skillList);
                }
            }
            $score += min($totalSkills * 1.5, 15);
        }

        // Additional sections (10 points)
        if (!empty($data['objective'])) $score += 3;
        if (!empty($data['languages'])) $score += 3;
        if (!empty($data['certifications'])) $score += 4;

        return round(min($score, $maxScore), 2);
    }

    /**
     * Parse CV from file path
     */
    public function parseFromPath(string $filePath, array $options = [])
    {
        try {
            if (!file_exists($filePath)) {
                throw new Exception("File not found: {$filePath}");
            }

            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

            if (!in_array($extension, $this->supportedFormats)) {
                throw new Exception("Unsupported file format: {$extension}");
            }

            $text = $this->extractTextFromPath($filePath, $extension);
            $cleanText = $this->cleanText($text);
            $parsedData = $this->parseCV($cleanText);
            $confidenceScore = $this->calculateConfidenceScore($parsedData, $cleanText);

            return [
                'success' => true,
                'message' => 'CV parsed successfully from path',
                'data' => array_merge($parsedData, [
                    'metadata' => [
                        'confidence_score' => $confidenceScore,
                        'parsed_at' => Carbon::now()->toISOString(),
                        'file_path' => $filePath,
                        'file_type' => $extension,
                        'text_length' => strlen($cleanText)
                    ]
                ])
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to parse CV: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Extract text from file path
     */
    protected function extractTextFromPath($path, $extension)
    {
        switch (strtolower($extension)) {
            case 'pdf':
                return $this->extractPdfText($path);
            case 'doc':
            case 'docx':
                return $this->extractWordText($path);
            case 'txt':
            case 'rtf':
                return $this->extractPlainText($path);
            default:
                throw new Exception("Unsupported file extension: {$extension}");
        }
    }

    /**
     * Extract contact information only (lightweight parsing)
     */
    public function extractContactOnly(UploadedFile $file)
    {
        try {
            $this->validateFile($file);
            $extension = strtolower($file->getClientOriginalExtension());
            $text = $this->extractTextAdvanced($file, $extension);
            $cleanText = $this->cleanText($text);

            $contactInfo = [
                'name' => $this->extractName($cleanText),
                'email' => $this->extractEmail($cleanText),
                'phone' => $this->extractPhone($cleanText),
                'location' => $this->extractLocation($cleanText)
            ];

            $contactInfo = array_filter($contactInfo, function($value) {
                return !empty($value);
            });

            return [
                'success' => true,
                'message' => 'Contact information extracted successfully',
                'data' => [
                    'contact_info' => $contactInfo,
                    'confidence' => $this->calculateContactConfidence($contactInfo)
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to extract contact information: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Calculate contact confidence
     */
    protected function calculateContactConfidence($contactInfo)
    {
        $score = 0;
        if (!empty($contactInfo['name'])) $score += 30;
        if (!empty($contactInfo['email'])) $score += 25;
        if (!empty($contactInfo['phone'])) $score += 25;
        if (!empty($contactInfo['location'])) $score += 20;

        return round($score, 2);
    }

    /**
     * Get supported formats
     */
    public function getSupportedFormats()
    {
        return $this->supportedFormats;
    }

    /**
     * Clean up stored files
     */
    public function cleanupStoredFile($filePath)
    {
        try {
            if (Storage::exists($filePath)) {
                Storage::delete($filePath);
                return true;
            }
            return false;
        } catch (Exception $e) {
            Log::warning('Failed to cleanup stored file', ['path' => $filePath, 'error' => $e->getMessage()]);
            return false;
        }
    }
}
