<?php

namespace WigglePuppy\Rector\Rebranding;

use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Psr\Log\LoggerInterface;

/**
 * Replaces 'WordPress' with 'WigglePuppy' in string literals, preserving case.
 */
final class WordPressToWigglePuppyStringRector extends AbstractRector
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

    /**
     * Log a skipped string with the reason
     */
    private function logSkippedString(string $content, string $reason, string $file = null, int $line = null): void
    {
        // Log to PSR logger if available
        if ($this->logger) {
            $this->logger->info("Skipped string: {$content} - Reason: {$reason}");
        }

        // Always log to file
        $fileInfo = $file ? " in {$file}" : "";
        $lineInfo = $line ? " on line {$line}" : "";
        $logEntry = date('Y-m-d H:i:s') . "{$fileInfo}{$lineInfo}: Skipped: `{$content}` - Reason: {$reason}\n";
        file_put_contents($this->logFile, $logEntry, FILE_APPEND);
    }

    public function refactor(Node $node): ?Node
    {
        if (!$node instanceof String_) {
            return null;
        }

        $content = $node->value;

        // Skip if the string doesn't contain "wordpress" in any form
        if (!preg_match('/wordpress/i', $content)) {
            return null;
        }

        // Get file and line information for logging
        $fileInfo = $node->getAttribute('file');
        $lineInfo = $node->getAttribute('line');

        // Check for package/module names
        if (preg_match('/package\s+[a-zA-Z0-9_]*wordpress[a-zA-Z0-9_]*/i', $content) ||
            preg_match('/module\s+[a-zA-Z0-9_]*wordpress[a-zA-Z0-9_]*/i', $content)) {
            $this->logSkippedString($content, 'Package or module name', $fileInfo, $lineInfo);
            return null;
        }

        // Skip if the string contains patterns we shouldn't replace
        if (preg_match('/wordpress\.org|wordpress\.com/i', $content)) {
            $this->logSkippedString($content, 'URL or domain', $fileInfo, $lineInfo);
            return null;
        }

        if (preg_match('/function.*wordpress|class.*wordpress|\$.*wordpress/i', $content)) {
            $this->logSkippedString($content, 'PHP identifier', $fileInfo, $lineInfo);
            return null;
        }

        if (preg_match('/copyright.*wordpress|author.*wordpress/i', $content)) {
            $this->logSkippedString($content, 'Copyright notice or author attribution', $fileInfo, $lineInfo);
            return null;
        }

        // Skip if the string contains both "wordpress" and "wigglepuppy" (co-occurrence)
        if (preg_match('/.*wordpress.*wigglepuppy.*|.*wigglepuppy.*wordpress.*/i', $content)) {
            $this->logSkippedString($content, 'Co-occurrence of WordPress and WigglePuppy', $fileInfo, $lineInfo);
            return null;
        }

        // Replace WordPress with WigglePuppy preserving case and whitespace
        // Using word boundary (\b) ensures we only replace the exact word and preserve surrounding whitespace
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
