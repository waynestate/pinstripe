<?php

declare(strict_types=1);

namespace WayneState\Pinstripe\Tests;

use Nette\Neon\Neon;
use PHPUnit\Framework\TestCase;
use WayneState\Pinstripe\PHPStan\CurlVerifyOffRule;

/**
 * Keeps every banned call's errorTip pointing at both the Pinstripe page
 * and the CREED handbook rule it enforces, and every identifier inside the
 * pinstripe.banned. namespace. Mostly plain string work over the NEON text;
 * nette/neon reads it once to prove no entry goes without its tip.
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

    public function testEveryBanCarriesAnIdentifierAndATip(): void
    {
        // The line scan above only checks the tips that exist; this reads the
        // NEON for real, so an entry that forgot its tip (the static
        // withoutVerifying() entry once did) fails here.
        $parameters = Neon::decodeFile(dirname(__DIR__) . '/phpstan/disallowed.neon')['parameters'];
        $bare = [];
        $count = 0;

        foreach (['disallowedFunctionCalls', 'disallowedMethodCalls', 'disallowedStaticCalls'] as $list) {
            foreach ($parameters[$list] as $index => $entry) {
                $count++;

                foreach (['message', 'errorIdentifier', 'errorTip'] as $key) {
                    if (! is_string($entry[$key] ?? null) || $entry[$key] === '') {
                        $bare[] = "{$list}[{$index}] has no {$key}";
                    }
                }
            }
        }

        $this->assertGreaterThanOrEqual(20, $count, 'expected the ban lists in phpstan/disallowed.neon');
        $this->assertSame([], $bare);
    }

    public function testEveryErrorIdentifierStartsWithPinstripeBanned(): void
    {
        foreach ($this->errorIdentifierValues() as $identifier) {
            $this->assertStringStartsWith('pinstripe.banned.', $identifier);
        }
    }

    public function testTheCurlRuleKeepsTheSameLinksAndNamespace(): void
    {
        // The one ban that is a PHP rule, not a NEON entry, holds to the
        // same promises as the rest of the file.
        $this->assertStringContainsString(self::PINSTRIPE_PAGE, CurlVerifyOffRule::TIP);
        $this->assertStringContainsString(self::CREED_RULE, CurlVerifyOffRule::TIP);
        $this->assertStringStartsWith('pinstripe.banned.', CurlVerifyOffRule::IDENTIFIER);
        $this->assertStringContainsString('- ' . CurlVerifyOffRule::class, (string) file_get_contents(dirname(__DIR__) . '/phpstan/disallowed.neon'));
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
