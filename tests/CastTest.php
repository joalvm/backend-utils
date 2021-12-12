<?php

declare(strict_types=1);

namespace Joalvm\Tests;

use Joalvm\Utils\Cast;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 * @coversNothing
 */
class CastTest extends TestCase
{
    public function testRemueveEspaciosVacios(): void
    {
        $this->assertSame('string', Cast::toStr('  string  '));
    }

    public function testRetornaNullCuandoEsUnStringVacio(): void
    {
        $this->assertNull(Cast::toStr(''));
    }

    public function testRetornaNullCuandoElValorNoEsStringable(): void
    {
        $this->assertNull(Cast::toStr(new stdClass()));
    }

    public function testConvierteUnStringAEntero(): void
    {
        $this->assertIsInt(Cast::toInt('23'));
    }

    public function testConvierteCualquierValorNumericoAEntero(): void
    {
        $this->assertIsInt(Cast::toInt('23.5'));
    }

    public function testConvierteUnValorFlotanteAEntero(): void
    {
        $this->assertIsInt(Cast::toInt(23.5));
    }

    public function testRetornaNullCuandoNoEsUnValorNumerico(): void
    {
        $this->assertNull(Cast::toInt('NoNumericValue_23'));
    }

    public function testRetornaNullCuandoExistenEspacios(): void
    {
        $this->assertNull(Cast::toInt(' 23 '));
    }
}
