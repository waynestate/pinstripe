<?php

declare(strict_types=1);

namespace WayneState\Pinstripe\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Keeps every banned call's errorTip pointing at both the Pinstripe page
 * and the CREED handbook rule it enforces, and every identifier inside the
 * pinstripe.banned. namespace. Plain string work over the NEON text; no
 * parser needed.
 */
final class BannedCallLinksTest extends TestCase
{
    private const PINSTRIPE_PAGE = 'https://pinstripe.apps.wayne.edu/docs/reference/banned-calls.html';

    private const CREED_RULE = 'https://creed.apps.wayne.edu/handbook/code.html#banned-patterns';

    public function testEveryErrorTipLinksToThePinstripePageAndTheCreedRule(): void
    {
        $tips = $this->errorTipLines();

        $this->assertGreaterThanOrEqual(6, count($tips), 'expected at least six errorTip lines in phpstan/disallowed.neon');

        foreach ($tips as $tip) {
            $this->assertStringContainsString(self::PINSTRIPE_PAGE, $tip);
            $this->assertStringContainsString(self::CREED_RULE, $tip);
        }
    }

    public function testEveryErrorIdentifierStartsWithPinstripeBanned(): void
    {
        foreach ($this->errorIdentifierValues() as $identifier) {
            $this->assertStringStartsWith('pinstripe.banned.', $identifier);
        }
    }

    /**
     * The value of every `errorTip:` line in phpstan/disallowed.neon.
     *
     * @return list<string>
     */
    private function errorTipLines(): array
    {
        return $this->lineValues('/^\s*errorTip:\s*(.*)$/');
    }

    /**
     * The value of every `errorIdentifier:` line in phpstan/disallowed.neon.
     *
     * @return list<string>
     */
    private function errorIdentifierValues(): array
    {
        return $this->lineValues('/^\s*errorIdentifier:\s*\'(.*)\'$/');
    }

    /**
     * Capture group one of every line in phpstan/disallowed.neon that the
     * pattern matches.
     *
     * @return list<string>
     */
    private function lineValues(string $pattern): array
    {
        $text = (string) file_get_contents(dirname(__DIR__) . '/phpstan/disallowed.neon');
        $values = [];

        foreach (explode("\n", $text) as $line) {
            if (preg_match($pattern, $line, $matches) === 1) {
                $values[] = $matches[1];
            }
        }

        return $values;
    }
}
