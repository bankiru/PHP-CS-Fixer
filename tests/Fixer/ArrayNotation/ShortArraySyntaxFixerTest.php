<?php

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Tests\Fixer\ArrayNotation;

use PhpCsFixer\Test\AbstractFixerTestCase;

/**
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * @internal
 */
final class ShortArraySyntaxFixerTest extends AbstractFixerTestCase
{
    /**
     * @dataProvider provideExamples
     */
    public function testFix($expected, $input = null)
    {
        $this->doTest($expected, $input);
    }

    public function provideExamples()
    {
        return array(
            array('<?php $x = [];', '<?php $x = array();'),
            array('<?php $x = []; $y = [];', '<?php $x = array(); $y = array();'),
            array('<?php $x = [ ];', '<?php $x = array( );'),
            array('<?php $x = [\'foo\'];', '<?php $x = array(\'foo\');'),
            array('<?php $x = [ \'foo\' ];', '<?php $x = array( \'foo\' );'),
            array('<?php $x = [($y ? true : false)];', '<?php $x = array(($y ? true : false));'),
            array('<?php $x = [($y ? [true] : [false])];', '<?php $x = array(($y ? array(true) : array(false)));'),
            array('<?php $x = [($y ? [true] : [ false ])];', '<?php $x = array(($y ? array(true) : array( false )));'),
            array('<?php $x = [($y ? ["t" => true] : ["f" => false])];', '<?php $x = array(($y ? array("t" => true) : array("f" => false)));'),
            array('<?php print_r([($y ? true : false)]);', '<?php print_r(array(($y ? true : false)));'),
            array('<?php $x = [[[]]];', '<?php $x = array(array(array()));'),
            array('<?php $x = [[[]]]; $y = [[[]]];', '<?php $x = array(array(array())); $y = array(array(array()));'),
            array('<?php function(array $foo = []) {};', '<?php function(array $foo = array()) {};'),
            array('<?php function(array $foo) {};'),
            array('<?php function(array $foo = []) {};', '<?php function(array $foo = array()) {};'),
        );
    }
}
