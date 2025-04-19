<?php

namespace WigglePuppy\Rector\Rebranding;

use PhpParser\Comment;
use PhpParser\Node;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Replaces 'WordPress' with 'WigglePuppy' in comments, preserving case.
 */
final class WordPressToWigglePuppyCommentRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Replaces WordPress with WigglePuppy in comments, preserving case',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
// This is a WordPress function
function example() {
    // wordpress is great
    return true; // WORDPRESS RULES
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
// This is a WigglePuppy function
function example() {
    // wigglepuppy is great
    return true; // WIGGLEPUPPY RULES
}
CODE_SAMPLE
                ),
            ]
        );
    }

    public function getNodeTypes(): array
    {
        return [Node::class];
    }

    public function refactor(Node $node): ?Node
    {
        // Get all comments attached to this node
        $comments = $node->getComments();
        if (empty($comments)) {
            return null;
        }

        $modified = false;

        foreach ($comments as $key => $comment) {
            $text = $comment->getText();

            // Skip if the comment contains patterns we shouldn't replace
            if (preg_match('/wordpress\.org|wordpress\.com|function.*wordpress|class.*wordpress|\$.*wordpress|copyright.*wordpress|author.*wordpress/i', $text)) {
                continue;
            }

            // Skip if the comment contains both "wordpress" and "wigglepuppy" (co-occurrence)
            if (preg_match('/.*wordpress.*wigglepuppy.*|.*wigglepuppy.*wordpress.*/i', $text)) {
                continue;
            }

            // Replace WordPress with WigglePuppy preserving case and whitespace
            // Using word boundary (\b) ensures we only replace the exact word and preserve surrounding whitespace
            $newText = preg_replace('/\bWordPress\b/', 'WigglePuppy', $text);
            $newText = preg_replace('/\bwordpress\b/', 'wigglepuppy', $newText);
            $newText = preg_replace('/\bWORDPRESS\b/', 'WIGGLEPUPPY', $newText);

            if ($newText !== $text) {
                // Create a new comment with the replaced text
                $newComment = new Comment($newText, $comment->getStartLine(), $comment->getStartFilePos(), $comment->getStartTokenPos(), $comment->getEndLine(), $comment->getEndFilePos(), $comment->getEndTokenPos());
                $comments[$key] = $newComment;
                $modified = true;
            }
        }

        if ($modified) {
            $node->setAttribute('comments', $comments);
            return $node;
        }

        return null;
    }
}
