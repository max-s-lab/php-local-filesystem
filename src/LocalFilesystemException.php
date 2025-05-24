<?php

declare(strict_types=1);

namespace MaxSLab\Filesystem\Local;

use Exception;
use Throwable;

/**
 * @author Maksim Spirkov <spirkov.2001@mail.ru>
 */
class LocalFilesystemException extends Exception
{
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct("Local filesystem error: {$message}", $code, $previous);
    }
}
