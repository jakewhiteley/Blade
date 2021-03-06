<?php

namespace Bladezero\Tests\Illuminate\View\Blade;

class BladeExtendsTest extends AbstractBladeTestCase
{
    public function testExtendsAreCompiled()
    {
        $string = '@extends(\'foo\')
test';
        $expected = 'test'.PHP_EOL.'<?php echo $__env->make(\'foo\', \Tightenco\Collect\Support\Arr::except(get_defined_vars(), [\'__data\', \'__path\'])); ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));

        $string = '@extends(name(foo))'.PHP_EOL.'test';
        $expected = 'test'.PHP_EOL.'<?php echo $__env->make(name(foo), \Tightenco\Collect\Support\Arr::except(get_defined_vars(), [\'__data\', \'__path\'])); ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }

    public function testSequentialCompileStringCalls()
    {
        $string = '@extends(\'foo\')
test';
        $expected = 'test'.PHP_EOL.'<?php echo $__env->make(\'foo\', \Tightenco\Collect\Support\Arr::except(get_defined_vars(), [\'__data\', \'__path\'])); ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));

        // use the same compiler instance to compile another template with @extends directive
        $string = '@extends(name(foo))'.PHP_EOL.'test';
        $expected = 'test'.PHP_EOL.'<?php echo $__env->make(name(foo), \Tightenco\Collect\Support\Arr::except(get_defined_vars(), [\'__data\', \'__path\'])); ?>';
        $this->assertEquals($expected, $this->compiler->compileString($string));
    }
}
