<?php

namespace Rapier\Tests\View\Blade;

class BladeStopSectionTest extends AbstractBladeTestCase
{
    public function testStopSectionsAreCompiled()
    {
        $this->assertSame('<?php $__env->stopSection(); ?>', $this->compiler->compileString('@stop'));
    }
}
