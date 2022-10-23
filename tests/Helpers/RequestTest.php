<?php

declare(strict_types=1);

namespace Joalvm\Tests\Helpers;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class RequestTest extends TestCase
{
    public function testParamStr(): void
    {
        $this->assertSame('string', param_str(' <?s string  '));
    }
}
