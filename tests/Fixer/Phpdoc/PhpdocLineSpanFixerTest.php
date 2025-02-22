<?php

declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Tests\Fixer\Phpdoc;

use PhpCsFixer\Tests\Test\AbstractFixerTestCase;

/**
 * @author Gert de Pagter <BackEndTea@gmail.com>
 *
 * @internal
 *
 * @covers \PhpCsFixer\Fixer\Phpdoc\PhpdocLineSpanFixer
 */
final class PhpdocLineSpanFixerTest extends AbstractFixerTestCase
{
    /**
     * @dataProvider provideFixCases
     */
    public function testFix(string $expected, ?string $input = null, array $config = []): void
    {
        $this->fixer->configure($config);
        $this->doTest($expected, $input);
    }

    public function provideFixCases(): array
    {
        return [
            'It does not change doc blocks if not needed' => [
                '<?php

class Foo
{
    /**
     * Important
     */
    const FOO_BAR = "foobar";

    /**
     * @var bool
     */
    public $variable = true;

    /**
     * @var bool
     */
    private $var = false;


    /**
     * @return void
     */
    public function hello() {}
}
',
            ],
            'It does change doc blocks to multi by default' => [
                '<?php

class Foo
{
    /**
     * Important
     */
    const FOO_BAR = "foobar";

    /**
     * @var bool
     */
    public $variable = true;

    /**
     * @var bool
     */
    private $var = false;


    /**
     * @return void
     */
    public function hello() {}
}
',
                '<?php

class Foo
{
    /** Important */
    const FOO_BAR = "foobar";

    /** @var bool */
    public $variable = true;

    /** @var bool */
    private $var = false;


    /** @return void */
    public function hello() {}
}
',
            ],
            'It does change doc blocks to single if configured to do so' => [
                '<?php

class Foo
{
    /** Important */
    const FOO_BAR = "foobar";

    /** @var bool */
    public $variable = true;

    /** @var bool */
    private $var = false;


    /** @return void */
    public function hello() {}
}
',
                '<?php

class Foo
{
    /**
     * Important
     */
    const FOO_BAR = "foobar";

    /**
     * @var bool
     */
    public $variable = true;

    /**
     * @var bool
     */
    private $var = false;


    /**
     * @return void
     */
    public function hello() {}
}
',
                [
                    'property' => 'single',
                    'const' => 'single',
                    'method' => 'single',
                ],
            ],
            'It does change complicated doc blocks to single if configured to do so' => [
                '<?php

class Foo
{
    /** @var bool */
    public $variable1 = true;

    /** @var bool */
    public $variable2 = true;

    /** @Assert\File(mimeTypes={ "image/jpeg", "image/png" }) */
    public $imageFileObject;
}
',
                '<?php

class Foo
{
    /**
     * @var bool */
    public $variable1 = true;

    /** @var bool
     */
    public $variable2 = true;

    /**
     * @Assert\File(mimeTypes={ "image/jpeg", "image/png" })
     */
    public $imageFileObject;
}
',
                [
                    'property' => 'single',
                ],
            ],
            'It does not changes doc blocks from single if configured to do so' => [
                '<?php

class Foo
{
    /** Important */
    const FOO_BAR = "foobar";

    /** @var bool */
    public $variable = true;

    /** @var bool */
    private $var = false;


    /** @return void */
    public function hello() {}
}
',
                null,
                [
                    'property' => 'single',
                    'const' => 'single',
                    'method' => 'single',
                ],
            ],
            'It can be configured to change certain elements to single line' => [
                '<?php

class Foo
{
    /**
     * Important
     */
    const FOO_BAR = "foobar";

    /** @var bool */
    public $variable = true;

    /** @var bool */
    private $var = false;


    /**
     * @return void
     */
    public function hello() {}
}
',
                '<?php

class Foo
{
    /**
     * Important
     */
    const FOO_BAR = "foobar";

    /**
     * @var bool
     */
    public $variable = true;

    /**
     * @var bool
     */
    private $var = false;


    /**
     * @return void
     */
    public function hello() {}
}
',
                [
                    'property' => 'single',
                ],
            ],
            'It wont change a doc block to single line if it has multiple useful lines' => [
                '<?php

class Foo
{
    /**
     * Important
     * Really important
     */
    const FOO_BAR = "foobar";
}
',
                null,
                [
                    'const' => 'single',
                ],
            ],
            'It updates doc blocks correctly, even with more indentation' => [
                '<?php

if (false) {
    class Foo
    {
        /** @var bool */
        public $var = true;

        /**
         * @return void
         */
        public function hello () {}
    }
}
',
                '<?php

if (false) {
    class Foo
    {
        /**
         * @var bool
         */
        public $var = true;

        /** @return void */
        public function hello () {}
    }
}
',
                [
                    'property' => 'single',
                ],
            ],
            'It can convert empty doc blocks' => [
                '<?php

class Foo
{
    /**
     *
     */
    const FOO = "foobar";

    /**  */
    private $foo;
}',
                '<?php

class Foo
{
    /**  */
    const FOO = "foobar";

    /**
     *
     */
    private $foo;
}',
                [
                    'property' => 'single',
                ],
            ],
            'It can update doc blocks of static properties' => [
                '<?php

class Bar
{
    /**
     * Important
     */
    public static $variable = "acme";
}
',
                '<?php

class Bar
{
    /** Important */
    public static $variable = "acme";
}
',
            ],
            'It can update doc blocks of properties that use the var keyword instead of public' => [
                '<?php

class Bar
{
    /**
     * Important
     */
    var $variable = "acme";
}
',
                '<?php

class Bar
{
    /** Important */
    var $variable = "acme";
}
',
            ],
            'It can update doc blocks of static that do not declare visibility' => [
                '<?php

class Bar
{
    /**
     * Important
     */
    static $variable = "acme";
}
',
                '<?php

class Bar
{
    /** Important */
    static $variable = "acme";
}
',
            ],
            'It does not change method doc blocks if configured to do so' => [
                '<?php

class Foo
{
    /** @return mixed */
    public function bar() {}

    /**
     * @return void
     */
    public function baz() {}
}',
                null,
                [
                    'method' => null,
                ],
            ],
            'It does not change property doc blocks if configured to do so' => [
                '<?php

class Foo
{
    /**
     * @var int
     */
    public $foo;

    /** @var mixed */
    public $bar;
}',
                null,
                [
                    'property' => null,
                ],
            ],
            'It does not change const doc blocks if configured to do so' => [
                '<?php

class Foo
{
    /**
     * @var int
     */
    public const FOO = 1;

    /** @var mixed */
    public const BAR = null;
}',
                null,
                [
                    'const' => null,
                ],
            ],
            'It can handle constants with visibility, does not crash on trait imports' => [
                '<?php
trait Bar
{}

class Foo
{
    /** whatever */
    use Bar;

    /**
     *
     */
    public const FOO = "foobar";

    /**  */
    private $foo;
}',
                '<?php
trait Bar
{}

class Foo
{
    /** whatever */
    use Bar;

    /**  */
    public const FOO = "foobar";

    /**
     *
     */
    private $foo;
}',
                [
                    'property' => 'single',
                ],
            ],
            'It can handle properties with type declaration' => [
                '<?php

class Foo
{
    /**  */
    private ?string $foo;
}',
                '<?php

class Foo
{
    /**
     *
     */
    private ?string $foo;
}',
                [
                    'property' => 'single',
                ],
            ],
            'It can handle properties with array type declaration' => [
                '<?php

class Foo
{
    /** @var string[] */
    private array $foo;
}',
                '<?php

class Foo
{
    /**
     * @var string[]
     */
    private array $foo;
}',
                [
                    'property' => 'single',
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideFix81Cases
     * @requires PHP 8.1
     */
    public function testFix81(string $expected, string $input = null, array $config = []): void
    {
        $this->fixer->configure($config);
        $this->doTest($expected, $input);
    }

    public function provideFix81Cases(): \Generator
    {
        yield 'readonly' => [
            '<?php

class Foo
{
    /** @var string[] */
    private readonly array $foo1;

    /** @var string[] */
    readonly private array $foo2;

    /** @var string[] */
    readonly array $foo3;
}',
            '<?php

class Foo
{
    /**
     * @var string[]
     */
    private readonly array $foo1;

    /**
     * @var string[]
     */
    readonly private array $foo2;

    /**
     * @var string[]
     */
    readonly array $foo3;
}',
            [
                'property' => 'single',
            ],
        ];

        yield [
            '<?php
class Foo
{
    /**
     * 0
     */
    const B0 = "0";

    /**
     * 1
     */
    final public const B1 = "1";

    /**
     * 2
     */
    public final const B2 = "2";

    /**
     * 3
     */
    final const B3 = "3";
}
',
            '<?php
class Foo
{
    /** 0 */
    const B0 = "0";

    /** 1 */
    final public const B1 = "1";

    /** 2 */
    public final const B2 = "2";

    /** 3 */
    final const B3 = "3";
}
',
        ];

        yield [
            '<?php
                enum Foo
                {
                    /**
                     * @return void
                     */
                    public function hello() {}
                }
            ',
            '<?php
                enum Foo
                {
                    /** @return void */
                    public function hello() {}
                }
            ',
        ];
    }
}
