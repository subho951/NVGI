<?php

namespace App\Exceptions;

use Exception;

class CVParsingException extends Exception
{
    /**
     * @var array
     */
    protected $data;

    /**
     * Create a new CV parsing exception instance.
     *
     * @param string $message
     * @param array $data
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $message, array $data = [], int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->data = $data;
    }

    /**
     * Get additional data about the exception
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}
