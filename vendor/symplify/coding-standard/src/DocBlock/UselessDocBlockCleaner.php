<?php

declare (strict_types=1);
namespace Symplify\CodingStandard\DocBlock;

use ECSPrefix202311\Nette\Utils\Strings;
use PhpCsFixer\Tokenizer\Token;
final class UselessDocBlockCleaner
{
    /**
     * @var string[]
     */
    private const CLEANING_REGEXES = [self::TODO_COMMENT_BY_PHPSTORM_REGEX, self::TODO_IMPLEMENT_METHOD_COMMENT_BY_PHPSTORM_REGEX, self::COMMENT_CLASS_REGEX, self::COMMENT_CONSTRUCTOR_CLASS_REGEX];
    /**
     * @see https://regex101.com/r/5fQJkz/2
     * @var string
     */
    private const TODO_IMPLEMENT_METHOD_COMMENT_BY_PHPSTORM_REGEX = '#\\/\\/ TODO: Implement .*\\(\\) method.$#';
    /**
     * @see https://regex101.com/r/zayQpv/1
     * @var string
     */
    private const TODO_COMMENT_BY_PHPSTORM_REGEX = '#\\/\\/ TODO: Change the autogenerated stub$#';
    /**
     * @see https://regex101.com/r/RzTdFH/4
     * @var string
     */
    private const COMMENT_CLASS_REGEX = '#(\\/\\*{2}\\s+?)?(\\*|\\/\\/)\\s+[cC]lass\\s+[^\\s]*(\\s+\\*\\/)?$#';
    /**
     * @see https://regex101.com/r/bzbxXz/2
     * @var string
     */
    private const COMMENT_CONSTRUCTOR_CLASS_REGEX = '#^\\s{0,}(\\/\\*{2}\\s+?)?(\\*|\\/\\/)\\s+[^\\s]*\\s+[Cc]onstructor\\.?(\\s+\\*\\/)?$#';
    /**
     * @param Token[] $tokens
     */
    public function clearDocTokenContent(array $tokens, int $position, Token $currentToken) : string
    {
        $docContent = $currentToken->getContent();
        foreach (self::CLEANING_REGEXES as $cleaningRegex) {
            $docContent = Strings::replace($docContent, $cleaningRegex, '');
        }
        return $docContent;
    }
}
