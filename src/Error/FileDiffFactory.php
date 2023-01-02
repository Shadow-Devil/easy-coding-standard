<?php

declare(strict_types=1);

namespace Symplify\EasyCodingStandard\Error;

use ECSPrefix202301\Symplify\PackageBuilder\Console\Formatter\ColorConsoleDiffFormatter;
use ECSPrefix202301\Symplify\SmartFileSystem\SmartFileInfo;
use PHP_CodeSniffer\Sniffs\Sniff;
use PhpCsFixer\Fixer\FixerInterface;
use Symplify\EasyCodingStandard\ValueObject\Error\FileDiff;

final class FileDiffFactory
{
    /**
     * @var \ECSPrefix202301\Symplify\PackageBuilder\Console\Formatter\ColorConsoleDiffFormatter
     */
    private $colorConsoleDiffFormatter;

    public function __construct(ColorConsoleDiffFormatter $colorConsoleDiffFormatter)
    {
        $this->colorConsoleDiffFormatter = $colorConsoleDiffFormatter;
    }

    /**
     * @param array<class-string<FixerInterface|Sniff>|string> $appliedCheckers
     */
    public function createFromDiffAndAppliedCheckers(
        SmartFileInfo $smartFileInfo,
        string $diff,
        array $appliedCheckers
    ): FileDiff {
        $consoleFormattedDiff = $this->colorConsoleDiffFormatter->format($diff);
        return new FileDiff(
            $smartFileInfo->getRelativeFilePathFromCwd(),
            $diff,
            $consoleFormattedDiff,
            $appliedCheckers
        );
    }
}
