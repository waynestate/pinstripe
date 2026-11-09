<?php

declare(strict_types=1);

namespace WayneState\Pinstripe\PHPStan;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Reports curl_setopt() turning TLS certificate checks off:
 * CURLOPT_SSL_VERIFYPEER or CURLOPT_SSL_VERIFYHOST set to a falsy value.
 *
 * Everything else Pinstripe bans lives in phpstan/disallowed.neon, through
 * spaze/phpstan-disallowed-calls. That package matches each parameter on its
 * own (any one listed parameter is enough to report), so it cannot say "this
 * option AND that value", and banning the option alone would flag the apps
 * that set verification on explicitly. This rule is the missing AND and
 * nothing more; curl_setopt_array() is a single array argument, so it stays
 * in the NEON file. The message reads like the package's, so a report looks
 * the same whichever one found it.
 *
 * A tailor who skips the fitting still sends the suit out; this one checks
 * the buttons.
 *
 * @implements Rule<FuncCall>
 */
final class CurlVerifyOffRule implements Rule
{
    public const IDENTIFIER = 'pinstripe.banned.curlVerifyOff';

    public const MESSAGE = 'Calling curl_setopt() is forbidden, it turns off TLS certificate checks'
        . ' (CURLOPT_SSL_VERIFYPEER false or CURLOPT_SSL_VERIFYHOST 0), so anyone on the network path can read or'
        . ' change the traffic; keep verification on, and for a private CA point CURLOPT_CAINFO at its bundle'
        . ' (OWASP A02:2025 Security Misconfiguration).';

    public const TIP = 'See https://pinstripe.apps.wayne.edu/docs/reference/banned-calls.html and the CREED rule at'
        . ' https://creed.apps.wayne.edu/handbook/code.html#banned-patterns';

    /**
     * CURLOPT_SSL_VERIFYPEER (64) and CURLOPT_SSL_VERIFYHOST (81), by value,
     * so the rule loads even where PHPStan itself runs without ext-curl.
     */
    private const VERIFY_OPTIONS = [64, 81];

    public function __construct(private readonly ReflectionProvider $reflectionProvider)
    {
    }

    public function getNodeType(): string
    {
        return FuncCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->name instanceof Name || $node->isFirstClassCallable()) {
            return [];
        }

        if ($this->reflectionProvider->resolveFunctionName($node->name, $scope) !== 'curl_setopt') {
            return [];
        }

        $option = $this->argument($node, 1, 'option');
        $value = $this->argument($node, 2, 'value');
        if ($option === null || $value === null) {
            return [];
        }

        $options = $scope->getType($option)->getConstantScalarValues();
        if ($options === []) {
            return [];
        }

        foreach ($options as $candidate) {
            if (!in_array($candidate, self::VERIFY_OPTIONS, true)) {
                return [];
            }
        }

        if (!$scope->getType($value)->toBoolean()->isFalse()->yes()) {
            return [];
        }

        return [
            RuleErrorBuilder::message(self::MESSAGE)
                ->identifier(self::IDENTIFIER)
                ->tip(self::TIP)
                ->build(),
        ];
    }

    /**
     * The argument at a position, or passed by name; null when it is missing
     * or unpacked from an array, since then nobody can tell what it holds.
     */
    private function argument(FuncCall $call, int $position, string $name): ?Expr
    {
        foreach ($call->getArgs() as $index => $arg) {
            $matches = $arg->name !== null ? $arg->name->toString() === $name : $index === $position;
            if ($matches) {
                return $arg->unpack ? null : $arg->value;
            }
        }

        return null;
    }
}
