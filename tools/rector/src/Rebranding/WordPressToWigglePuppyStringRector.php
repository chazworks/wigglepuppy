<?php

namespace WigglePuppy\Rector\Rebranding;

use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Replaces 'WordPress' with 'WigglePuppy' in string literals, preserving case.
 */
final class WordPressToWigglePuppyStringRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replaces WordPress with WigglePuppy in string literals, preserving case',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
$text = "Welcome to WordPress";
$lowercase = "wordpress is great";
$uppercase = "WORDPRESS RULES";
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$text = "Welcome to WigglePuppy";
$lowercase = "wigglepuppy is great";
$uppercase = "WIGGLEPUPPY RULES";
CODE_SAMPLE
                ),
            ]
        );
    }

    public function getNodeTypes(): array
    {
        return [String_::class];
    }

    public function refactor(Node $node): ?Node
    {
        if (!$node instanceof String_) {
            return null;
        }

        $content = $node->value;

        // Skip if the string contains patterns we shouldn't replace
        if (preg_match('/wordpress\.org|wordpress\.com|function.*wordpress|class.*wordpress|\$.*wordpress|copyright.*wordpress|author.*wordpress/i', $content)) {
            return null;
        }

        // Skip if the string contains both "wordpress" and "wigglepuppy" (co-occurrence)
        if (preg_match('/.*wordpress.*wigglepuppy.*|.*wigglepuppy.*wordpress.*/i', $content)) {
            return null;
        }

        // Replace WordPress with WigglePuppy preserving case
        $newContent = preg_replace('/\bWordPress\b/', 'WigglePuppy', $content);
        $newContent = preg_replace('/\bwordpress\b/', 'wigglepuppy', $newContent);
        $newContent = preg_replace('/\bWORDPRESS\b/', 'WIGGLEPUPPY', $newContent);

        if ($newContent === $content) {
            return null;
        }

        $node->value = $newContent;
        return $node;
    }
}
