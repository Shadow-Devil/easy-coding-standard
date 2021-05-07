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
namespace PhpCsFixer\Fixer\LanguageConstruct;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\FixerConfiguration\AllowedValueSubset;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolver;
use PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface;
use PhpCsFixer\FixerConfiguration\FixerOptionBuilder;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\FixerDefinition\FixerDefinitionInterface;
use PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer;
use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Token;
use PhpCsFixer\Tokenizer\Tokens;
/**
 * @author SpacePossum
 */
final class FunctionToConstantFixer extends \PhpCsFixer\AbstractFixer implements \PhpCsFixer\Fixer\ConfigurableFixerInterface
{
    /**
     * @var array<string, Token[]>
     */
    private static $availableFunctions;
    /**
     * @var array<string, Token[]>
     */
    private $functionsFixMap;
    public function __construct()
    {
        if (null === self::$availableFunctions) {
            self::$availableFunctions = ['get_called_class' => [new \PhpCsFixer\Tokenizer\Token([\T_STATIC, 'static']), new \PhpCsFixer\Tokenizer\Token([\T_DOUBLE_COLON, '::']), new \PhpCsFixer\Tokenizer\Token([\PhpCsFixer\Tokenizer\CT::T_CLASS_CONSTANT, 'class'])], 'get_class' => [new \PhpCsFixer\Tokenizer\Token([\T_CLASS_C, '__CLASS__'])], 'get_class_this' => [new \PhpCsFixer\Tokenizer\Token([\T_STATIC, 'static']), new \PhpCsFixer\Tokenizer\Token([\T_DOUBLE_COLON, '::']), new \PhpCsFixer\Tokenizer\Token([\PhpCsFixer\Tokenizer\CT::T_CLASS_CONSTANT, 'class'])], 'php_sapi_name' => [new \PhpCsFixer\Tokenizer\Token([\T_STRING, 'PHP_SAPI'])], 'phpversion' => [new \PhpCsFixer\Tokenizer\Token([\T_STRING, 'PHP_VERSION'])], 'pi' => [new \PhpCsFixer\Tokenizer\Token([\T_STRING, 'M_PI'])]];
        }
        parent::__construct();
    }
    /**
     * {@inheritdoc}
     * @return void
     */
    public function configure(array $configuration)
    {
        parent::configure($configuration);
        $this->functionsFixMap = [];
        foreach ($this->configuration['functions'] as $key) {
            $this->functionsFixMap[$key] = self::$availableFunctions[$key];
        }
    }
    /**
     * {@inheritdoc}
     * @return \PhpCsFixer\FixerDefinition\FixerDefinitionInterface
     */
    public function getDefinition()
    {
        return new \PhpCsFixer\FixerDefinition\FixerDefinition('Replace core functions calls returning constants with the constants.', [new \PhpCsFixer\FixerDefinition\CodeSample("<?php\necho phpversion();\necho pi();\necho php_sapi_name();\nclass Foo\n{\n    public function Bar()\n    {\n        echo get_class();\n        echo get_called_class();\n    }\n}\n"), new \PhpCsFixer\FixerDefinition\CodeSample("<?php\necho phpversion();\necho pi();\nclass Foo\n{\n    public function Bar()\n    {\n        echo get_class();\n        get_class(\$this);\n        echo get_called_class();\n    }\n}\n", ['functions' => ['get_called_class', 'get_class_this', 'phpversion']])], null, 'Risky when any of the configured functions to replace are overridden.');
    }
    /**
     * {@inheritdoc}
     *
     * Must run before NativeFunctionCasingFixer, NoExtraBlankLinesFixer, NoSinglelineWhitespaceBeforeSemicolonsFixer, NoTrailingWhitespaceFixer, NoWhitespaceInBlankLineFixer, SelfStaticAccessorFixer.
     * Must run after NoSpacesAfterFunctionNameFixer, NoSpacesInsideParenthesisFixer.
     * @return int
     */
    public function getPriority()
    {
        return 1;
    }
    /**
     * {@inheritdoc}
     * @param \PhpCsFixer\Tokenizer\Tokens $tokens
     * @return bool
     */
    public function isCandidate($tokens)
    {
        return $tokens->isTokenKindFound(\T_STRING);
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
        $functionAnalyzer = new \PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer();
        for ($index = $tokens->count() - 4; $index > 0; --$index) {
            $candidate = $this->getReplaceCandidate($tokens, $functionAnalyzer, $index);
            if (null === $candidate) {
                continue;
            }
            $this->fixFunctionCallToConstant(
                $tokens,
                $index,
                $candidate[0],
                // brace open
                $candidate[1],
                // brace close
                $candidate[2]
            );
        }
    }
    /**
     * {@inheritdoc}
     * @return \PhpCsFixer\FixerConfiguration\FixerConfigurationResolverInterface
     */
    protected function createConfigurationDefinition()
    {
        $functionNames = \array_keys(self::$availableFunctions);
        return new \PhpCsFixer\FixerConfiguration\FixerConfigurationResolver([(new \PhpCsFixer\FixerConfiguration\FixerOptionBuilder('functions', 'List of function names to fix.'))->setAllowedTypes(['array'])->setAllowedValues([new \PhpCsFixer\FixerConfiguration\AllowedValueSubset($functionNames)])->setDefault(['get_called_class', 'get_class', 'get_class_this', 'php_sapi_name', 'phpversion', 'pi'])->getOption()]);
    }
    /**
     * @param Token[] $replacements
     * @return void
     * @param \PhpCsFixer\Tokenizer\Tokens $tokens
     * @param int $index
     * @param int $braceOpenIndex
     * @param int $braceCloseIndex
     */
    private function fixFunctionCallToConstant($tokens, $index, $braceOpenIndex, $braceCloseIndex, array $replacements)
    {
        for ($i = $braceCloseIndex; $i >= $braceOpenIndex; --$i) {
            if ($tokens[$i]->isGivenKind([\T_WHITESPACE, \T_COMMENT, \T_DOC_COMMENT])) {
                continue;
            }
            $tokens->clearTokenAndMergeSurroundingWhitespace($i);
        }
        if ($replacements[0]->isGivenKind([\T_CLASS_C, \T_STATIC])) {
            $prevIndex = $tokens->getPrevMeaningfulToken($index);
            $prevToken = $tokens[$prevIndex];
            if ($prevToken->isGivenKind(\T_NS_SEPARATOR)) {
                $tokens->clearAt($prevIndex);
            }
        }
        $tokens->clearAt($index);
        $tokens->insertAt($index, $replacements);
    }
    /**
     * @return mixed[]|null
     * @param \PhpCsFixer\Tokenizer\Tokens $tokens
     * @param \PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer $functionAnalyzer
     * @param int $index
     */
    private function getReplaceCandidate($tokens, $functionAnalyzer, $index)
    {
        if (!$tokens[$index]->isGivenKind(\T_STRING)) {
            return null;
        }
        $lowerContent = \strtolower($tokens[$index]->getContent());
        if ('get_class' === $lowerContent) {
            return $this->fixGetClassCall($tokens, $functionAnalyzer, $index);
        }
        if (!isset($this->functionsFixMap[$lowerContent])) {
            return null;
        }
        if (!$functionAnalyzer->isGlobalFunctionCall($tokens, $index)) {
            return null;
        }
        // test if function call without parameters
        $braceOpenIndex = $tokens->getNextMeaningfulToken($index);
        if (!$tokens[$braceOpenIndex]->equals('(')) {
            return null;
        }
        $braceCloseIndex = $tokens->getNextMeaningfulToken($braceOpenIndex);
        if (!$tokens[$braceCloseIndex]->equals(')')) {
            return null;
        }
        return $this->getReplacementTokenClones($lowerContent, $braceOpenIndex, $braceCloseIndex);
    }
    /**
     * @return mixed[]|null
     * @param \PhpCsFixer\Tokenizer\Tokens $tokens
     * @param \PhpCsFixer\Tokenizer\Analyzer\FunctionsAnalyzer $functionAnalyzer
     * @param int $index
     */
    private function fixGetClassCall($tokens, $functionAnalyzer, $index)
    {
        if (!isset($this->functionsFixMap['get_class']) && !isset($this->functionsFixMap['get_class_this'])) {
            return null;
        }
        if (!$functionAnalyzer->isGlobalFunctionCall($tokens, $index)) {
            return null;
        }
        $braceOpenIndex = $tokens->getNextMeaningfulToken($index);
        $braceCloseIndex = $tokens->findBlockEnd(\PhpCsFixer\Tokenizer\Tokens::BLOCK_TYPE_PARENTHESIS_BRACE, $braceOpenIndex);
        if ($braceCloseIndex === $tokens->getNextMeaningfulToken($braceOpenIndex)) {
            // no arguments passed
            if (isset($this->functionsFixMap['get_class'])) {
                return $this->getReplacementTokenClones('get_class', $braceOpenIndex, $braceCloseIndex);
            }
        } elseif (isset($this->functionsFixMap['get_class_this'])) {
            $isThis = \false;
            for ($i = $braceOpenIndex + 1; $i < $braceCloseIndex; ++$i) {
                if ($tokens[$i]->equalsAny([[\T_WHITESPACE], [\T_COMMENT], [\T_DOC_COMMENT], ')'])) {
                    continue;
                }
                if ($tokens[$i]->isGivenKind(\T_VARIABLE) && '$this' === \strtolower($tokens[$i]->getContent())) {
                    $isThis = \true;
                    continue;
                }
                if (\false === $isThis && $tokens[$i]->equals('(')) {
                    continue;
                }
                $isThis = \false;
                break;
            }
            if ($isThis) {
                return $this->getReplacementTokenClones('get_class_this', $braceOpenIndex, $braceCloseIndex);
            }
        }
        return null;
    }
    /**
     * @param string $lowerContent
     * @param int $braceOpenIndex
     * @param int $braceCloseIndex
     * @return mixed[]
     */
    private function getReplacementTokenClones($lowerContent, $braceOpenIndex, $braceCloseIndex)
    {
        $clones = [];
        foreach ($this->functionsFixMap[$lowerContent] as $token) {
            $clones[] = clone $token;
        }
        return [$braceOpenIndex, $braceCloseIndex, $clones];
    }
}
