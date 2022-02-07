<?php

/*
 * This file is part of the Hierarchy package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Brain\Hierarchy\Tests\Unit;

use Brain\Hierarchy\FileExtensionPredicate;
use Brain\Hierarchy\Tests\TestCase;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
class FileExtensionPredicateTest extends TestCase
{
    /**
     * @test
     * @dataProvider parseExtensionsProvider
     */
    public function testParseExtensions(string $input, array $output): void
    {
        static::assertEquals($output, FileExtensionPredicate::parseExtensions($input));
    }

    /**
     * @test
     */
    public function testSingleExtension(): void
    {
        /** @var callable $predicate */
        $predicate = new FileExtensionPredicate('php');

        static::assertTrue($predicate('index.php'));
        static::assertTrue($predicate('foo.PHP'));
        static::assertFalse($predicate('foo.phtml'));
        static::assertFalse($predicate('.phtml'));
    }

    /**
     * @test
     */
    public function testSingleExtensionNormalize(): void
    {
        /** @var callable $predicate */
        $predicate = new FileExtensionPredicate(' .pHP ');

        static::assertTrue($predicate('foo.php'));
        static::assertTrue($predicate('foo.PHP'));
        static::assertFalse($predicate('foo.phtml'));
    }

    /**
     * @test
     */
    public function testMultiExtensionString(): void
    {
        /** @var callable $predicate */
        $predicate = new FileExtensionPredicate(' php | PHTML | .inc ');

        static::assertTrue($predicate('foo.php'));
        static::assertTrue($predicate('foo.PHP'));
        static::assertTrue($predicate('foo.phtml'));
        static::assertTrue($predicate('foo.inc'));
        static::assertFalse($predicate('foo.twig'));
    }

    /**
     * @test
     */
    public function testMultiExtensionArray(): void
    {
        /** @var callable $predicate */
        $predicate = new FileExtensionPredicate(' php ', 'PHTML ', ' .inc');

        static::assertTrue($predicate('foo.php'));
        static::assertTrue($predicate('foo.PHP'));
        static::assertTrue($predicate('foo.phtml'));
        static::assertTrue($predicate('foo.inc'));
        static::assertFalse($predicate('foo.twig'));
    }

    /**
     * @return list<array{string, list<string>}>
     */
    public function parseExtensionsProvider(): array
    {
        return [
            ['php', ['php']],
            ['.html.php', ['html.php']],
            ["\0\n\t .PhP \0\n\t", ['php']],
            ['twig|php|html', ['twig', 'php', 'html']],
            ["\nTWIG | php\t | .Html", ['twig', 'php', 'html']],
        ];
    }
}
