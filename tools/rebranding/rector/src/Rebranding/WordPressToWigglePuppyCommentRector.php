<?php

namespace WigglePuppy\Rector\Rebranding;

use PhpParser\Comment;
use PhpParser\Node;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Psr\Log\LoggerInterface;

/**
 * Replaces 'WordPress' with 'WigglePuppy' in comments, preserving case.
 */
final class WordPressToWigglePuppyCommentRector extends AbstractRector
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $logFile;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        $this->logFile = dirname(dirname(dirname(dirname(dirname(__DIR__))))) . '/docs/rebranding/rector-skipped.log';

        // Create log file if it doesn't exist
        if (!file_exists($this->logFile)) {
            file_put_contents($this->logFile, "# WordPress to WigglePuppy Rector Skipped Items Log\n\n");
        }
    }
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

    /**
     * Log a skipped comment with the reason
     */
    private function logSkippedComment(string $text, string $reason, string $file = null, int $line = null): void
    {
        // Log to PSR logger if available
        if ($this->logger) {
            $this->logger->info("Skipped comment: {$text} - Reason: {$reason}");
        }

        // Always log to file
        $fileInfo = $file ? " in {$file}" : "";
        $lineInfo = $line ? " on line {$line}" : "";
        $logEntry = date('Y-m-d H:i:s') . "{$fileInfo}{$lineInfo}: Skipped comment: `{$text}` - Reason: {$reason}\n";
        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }

    public function refactor(Node $node): ?Node
    {
        // Get all comments attached to this node
        $comments = $node->getComments();
        if (empty($comments)) {
            return null;
        }

        // Get file and line information for logging
        $fileInfo = $node->getAttribute('file');
        $lineInfo = $node->getAttribute('line');

        $modified = false;

        foreach ($comments as $key => $comment) {
            $text = $comment->getText();

            // Skip if the comment doesn't contain "wordpress" in any form
            if (!preg_match('/wordpress/i', $text)) {
                continue;
            }

            // Check for package/module names
            if (preg_match('/package\s+[a-zA-Z0-9_]*wordpress[a-zA-Z0-9_]*/i', $text) ||
                preg_match('/module\s+[a-zA-Z0-9_]*wordpress[a-zA-Z0-9_]*/i', $text)) {
                $this->logSkippedComment($text, 'Package or module name', $fileInfo, $lineInfo);
                continue;
            }

            // Skip if the comment contains patterns we shouldn't replace
            if (preg_match('/wordpress\.org|wordpress\.com/i', $text)) {
                $this->logSkippedComment($text, 'URL or domain', $fileInfo, $lineInfo);
                continue;
            }

            if (preg_match('/function.*wordpress|class.*wordpress|\$.*wordpress/i', $text)) {
                $this->logSkippedComment($text, 'PHP identifier', $fileInfo, $lineInfo);
                continue;
            }

            if (preg_match('/copyright.*wordpress|author.*wordpress/i', $text)) {
                $this->logSkippedComment($text, 'Copyright notice or author attribution', $fileInfo, $lineInfo);
                continue;
            }

            // Skip if the comment contains both "wordpress" and "wigglepuppy" (co-occurrence)
            if (preg_match('/.*wordpress.*wigglepuppy.*|.*wigglepuppy.*wordpress.*/i', $text)) {
                $this->logSkippedComment($text, 'Co-occurrence of WordPress and WigglePuppy', $fileInfo, $lineInfo);
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
