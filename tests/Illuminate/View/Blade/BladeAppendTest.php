<?php

namespace Rapier\Tests\Illuminate\View\Blade;

class BladeAppendTest extends AbstractBladeTestCase
{
    public function testAppendSectionsAreCompiled()
    {
        $this->assertSame('<?php $__env->appendSection(); ?>', $this->compiler->compileString('@append'));
    }
}
