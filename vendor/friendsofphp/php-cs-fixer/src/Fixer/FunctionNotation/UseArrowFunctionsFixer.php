<?php

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace PhpCsFixer\Fixer\FunctionNotation;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\FixerDefinition\VersionSpecification;
use PhpCsFixer\FixerDefinition\VersionSpecificCodeSample;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;
/**
 * @author Gregor Harlan
 */
final class UseArrowFunctionsFixer extends \PhpCsFixer\AbstractFixer
{
    /**
     * {@inheritdoc}
     * @return \PhpCsFixer\FixerDefinition\FixerDefinitionInterface
     */
    public function getDefinition()
    {
        return new \PhpCsFixer\FixerDefinition\FixerDefinition('Anonymous functions with one-liner return statement must use arrow functions.', [new \PhpCsFixer\FixerDefinition\VersionSpecificCodeSample(<<<'SAMPLE'
<?php

namespace ECSPrefix20210507;

\ECSPrefix20210507\foo(function ($a) use($b) {
    return $a + $b;
});

SAMPLE
, new \PhpCsFixer\FixerDefinition\VersionSpecification(70400))], null, 'Risky when using `isset()` on outside variables that are not imported with `use ()`.');
    }
    /**
     * {@inheritdoc}
     * @param \PhpCsFixer\Tokenizer\Tokens $tokens
     * @return bool
     */
    public function isCandidate($tokens)
    {
        return \PHP_VERSION_ID >= 70400 && $tokens->isAllTokenKindsFound([\T_FUNCTION, \T_RETURN]);
    }
    /**
     * {@inheritdoc}
     * @return bool
     */
    public function isRisky()
    {
        return \true;
    }
    /**
     * {@inheritdoc}
     * @return void
     * @param \SplFileInfo $file
     * @param \PhpCsFixer\Tokenizer\Tokens $tokens
     */
    protected function applyFix($file, $tokens)
    {
        $analyzer = new \PhpCsFixer\Tokenizer\TokensAnalyzer($tokens);
        for ($index = $tokens->count() - 1; $index > 0; --$index) {
            if (!$tokens[$index]->isGivenKind(\T_FUNCTION) || !$analyzer->isLambda($index)) {
                continue;
            }
            // Find parameters end
            // Abort if they are multilined
            $parametersStart = $tokens->getNextMeaningfulToken($index);
            if ($tokens[$parametersStart]->isGivenKind(\PhpCsFixer\Tokenizer\CT::T_RETURN_REF)) {
                $parametersStart = $tokens->getNextMeaningfulToken($parametersStart);
            }
            $parametersEnd = $tokens->findBlockEnd(\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $parametersStart);
            if ($this->isMultilined($tokens, $parametersStart, $parametersEnd)) {
                continue;
            }
            // Find `use ()` start and end
            // Abort if it contains reference variables
            $next = $tokens->getNextMeaningfulToken($parametersEnd);
            $useStart = null;
            $useEnd = null;
            if ($tokens[$next]->isGivenKind(\PhpCsFixer\Tokenizer\CT::T_USE_LAMBDA)) {
                $useStart = $next;
                if ($tokens[$useStart - 1]->isGivenKind(\T_WHITESPACE)) {
                    --$useStart;
                }
                $next = $tokens->getNextMeaningfulToken($next);
                while (!$tokens[$next]->equals(')')) {
                    if ($tokens[$next]->equals('&')) {
                        // variables used by reference are not supported by arrow functions
                        continue 2;
                    }
                    $next = $tokens->getNextMeaningfulToken($next);
                }
                $useEnd = $next;
                $next = $tokens->getNextMeaningfulToken($next);
            }
            // Find opening brace and following `return`
            // Abort if there is more than whitespace between them (like comments)
            $braceOpen = $tokens[$next]->equals('{') ? $next : $tokens->getNextTokenOfKind($next, ['{']);
            $return = $braceOpen + 1;
            if ($tokens[$return]->isGivenKind(\T_WHITESPACE)) {
                ++$return;
            }
            if (!$tokens[$return]->isGivenKind(\T_RETURN)) {
                continue;
            }
            // Find semicolon of `return` statement
            $semicolon = $tokens->getNextTokenOfKind($return, ['{', ';']);
            if (!$tokens[$semicolon]->equals(';')) {
                continue;
            }
            // Find closing brace
            // Abort if there is more than whitespace between semicolon and closing brace
            $braceClose = $semicolon + 1;
            if ($tokens[$braceClose]->isGivenKind(\T_WHITESPACE)) {
                ++$braceClose;
            }
            if (!$tokens[$braceClose]->equals('}')) {
                continue;
            }
            // Abort if the `return` statement is multilined
            if ($this->isMultilined($tokens, $return, $semicolon)) {
                continue;
            }
            // Transform the function to an arrow function
            $this->transform($tokens, $index, $useStart, $useEnd, $braceOpen, $return, $semicolon, $braceClose);
        }
    }
    /**
     * @param \PhpCsFixer\Tokenizer\Tokens $tokens
     * @param int $start
     * @param int $end
     * @return bool
     */
    private function isMultilined($tokens, $start, $end)
    {
        for ($i = $start; $i < $end; ++$i) {
            if (\false !== \strpos($tokens[$i]->getContent(), "\n")) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param int|null $useStart
     * @param int|null $useEnd
     * @return void
     * @param \PhpCsFixer\Tokenizer\Tokens $tokens
     * @param int $index
     * @param int $braceOpen
     * @param int $return
     * @param int $semicolon
     * @param int $braceClose
     */
    private function transform($tokens, $index, $useStart, $useEnd, $braceOpen, $return, $semicolon, $braceClose)
    {
        $tokens->clearRange($semicolon, $braceClose);
        $tokens->clearRange($braceOpen + 1, $return);
        $tokens[$braceOpen] = new \PhpCsFixer\Tokenizer\Token([\T_DOUBLE_ARROW, '=>']);
        if ($useStart) {
            $tokens->clearRange($useStart, $useEnd);
        }
        $tokens[$index] = new \PhpCsFixer\Tokenizer\Token([\T_FN, 'fn']);
    }
}