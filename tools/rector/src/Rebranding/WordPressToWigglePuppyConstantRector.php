<?php

namespace WigglePuppy\Rector\Rebranding;

use PhpParser\Node;
use PhpParser\Node\Const_;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Replaces 'WORDPRESS' with 'WIGGLEPUPPY' in constant names and values.
 */
final class WordPressToWigglePuppyConstantRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replaces WORDPRESS with WIGGLEPUPPY in constant names and values',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
const WORDPRESS_VERSION = '5.8';
define('WORDPRESS_DEBUG', true);
$value = WORDPRESS_CONSTANT;
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
const WIGGLEPUPPY_VERSION = '5.8';
define('WIGGLEPUPPY_DEBUG', true);
$value = WIGGLEPUPPY_CONSTANT;
CODE_SAMPLE
                ),
            ]
        );
    }

    public function getNodeTypes(): array
    {
        return [
            Const_::class,
            ConstFetch::class,
            String_::class,
        ];
    }

    public function refactor(Node $node): ?Node
    {
        // Handle constant definitions (const WORDPRESS_X = ...)
        if ($node instanceof Const_) {
            $name = $this->getName($node);
            if ($name === null) {
                return null;
            }

            // Skip if the constant name contains patterns we shouldn't replace
            if ($this->shouldSkip($name)) {
                return null;
            }

            // Replace WORDPRESS with WIGGLEPUPPY in constant name
            $newName = preg_replace('/\bWORDPRESS\b/', 'WIGGLEPUPPY', $name);
            if ($newName !== $name) {
                $node->name = new \PhpParser\Node\Identifier($newName);
                return $node;
            }
        }

        // Handle constant fetches ($value = WORDPRESS_X)
        if ($node instanceof ConstFetch) {
            $name = $this->getName($node);
            if ($name === null) {
                return null;
            }

            // Skip if the constant name contains patterns we shouldn't replace
            if ($this->shouldSkip($name)) {
                return null;
            }

            // Replace WORDPRESS with WIGGLEPUPPY in constant name
            $newName = preg_replace('/\bWORDPRESS\b/', 'WIGGLEPUPPY', $name);
            if ($newName !== $name) {
                $node->name = new Name($newName);
                return $node;
            }
        }

        // Handle string literals that might be used in define() calls
        if ($node instanceof String_) {
            $value = $node->value;

            // Only process strings that look like constant names (all caps with underscores)
            if (!preg_match('/^[A-Z0-9_]+$/', $value)) {
                return null;
            }

            // Skip if the string contains patterns we shouldn't replace
            if ($this->shouldSkip($value)) {
                return null;
            }

            // Replace WORDPRESS with WIGGLEPUPPY in the string
            $newValue = preg_replace('/\bWORDPRESS\b/', 'WIGGLEPUPPY', $value);
            if ($newValue !== $value) {
                $node->value = $newValue;
                return $node;
            }
        }

        return null;
    }

    private function shouldSkip(string $name): bool
    {
        // Skip if the name contains patterns we shouldn't replace
        if (preg_match('/wordpress\.org|wordpress\.com|function.*wordpress|class.*wordpress|\$.*wordpress|copyright.*wordpress|author.*wordpress/i', $name)) {
            return true;
        }

        // Skip if the name contains both "wordpress" and "wigglepuppy" (co-occurrence)
        if (preg_match('/.*wordpress.*wigglepuppy.*|.*wigglepuppy.*wordpress.*/i', $name)) {
            return true;
        }

        return false;
    }
}
