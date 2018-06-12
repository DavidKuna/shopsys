<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\Sniffs;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use SlevomatCodingStandard\Helpers\ClassHelper;
use Symplify\TokenRunner\Analyzer\SnifferAnalyzer\Naming;

final class ObjectIsCreatedViaFactorySniff implements Sniff
{
    const FORBIDDEN_CLASSES = [
        'Factory',
    ];
    /**
     * @var Naming
     */
    private $naming;

    public function __construct(Naming $naming)
    {
        $this->naming = $naming;
    }

    /**
     * @return int[]
     */
    public function register(): array
    {
        return [T_NEW];
    }

    /**
     * @param File $file
     * @param int $position
     */
    public function process(File $file, $position): void
    {
        $fileClassName = ClassHelper::getFullyQualifiedName($file, $file->findNext(T_CLASS, 2));
        $className = '\\' . $this->naming->getClassName($file, $file->findNext(T_STRING, $position));
        $factoryName = $className . 'Factory';

        if ($factoryName === $fileClassName || !class_exists($factoryName)) {
            return;
        }

        $file->addError(
            sprintf('For creation of "%s" class use its factory "%s"', $className, $factoryName),
            $position,
            self::class
        );
    }
}
