<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\Sniffs;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

final class ControllerUsesOnlyFacadeSniff implements Sniff
{
    const FORBIDDEN_CLASSES = [
        'Factory',
    ];

    /**
     * @return int[]
     */
    public function register(): array
    {
        return [T_STRING];
    }

    /**
     * @param int $position
     */
    public function process(File $file, $position): void
    {
        if (preg_match('#.*Controller.php$#', $file->getFilename()) < 1) {
            return;
        }

        $currentToken = $file->getTokens()[$position];

        if (!in_array(preg_replace('#.+([A-Z].+)$#', '$1', $currentToken['content']), self::FORBIDDEN_CLASSES, true)) {
            return;
        }

        $file->addError(
            sprintf('Use of "%s" class or instance is forbidden in controller', $currentToken['content']),
            $position,
            self::class
        );
    }
}
