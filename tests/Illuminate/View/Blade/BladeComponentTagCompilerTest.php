<?php

namespace Bladezero\Tests\Illuminate\View\Blade;

use Bladezero\Container\Container;
use Bladezero\Contracts\Foundation\Application;
use Bladezero\Contracts\View\Factory;
use Bladezero\View\Compilers\ComponentTagCompiler;
use Bladezero\View\Component;
use Mockery;

class BladeComponentTagCompilerTest extends AbstractBladeTestCase
{
    public function tearDown(): void
    {
        Mockery::close();
    }

    public function testSlotsCanBeCompiled()
    {
        $result = (new ComponentTagCompiler)->compileSlots('<x-slot name="foo">
</x-slot>');

        $this->assertSame("@slot('foo') \n @endslot", trim($result));
    }

    public function testBasicComponentParsing()
    {
        $result = (new ComponentTagCompiler(['alert' => TestAlertComponent::class]))->compileTags('<div><x-alert type="foo" limit="5" @click="foo" required /><x-alert /></div>');

        $this->assertSame("<div> @component('Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent', [])
<?php \$component->withAttributes(['type' => 'foo','limit' => '5','@click' => 'foo','required' => true]); ?>
@endcomponentClass @component('Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent', [])
<?php \$component->withAttributes([]); ?>
@endcomponentClass</div>", trim($result));
    }

    public function testBasicComponentWithEmptyAttributesParsing()
    {
        $result = (new ComponentTagCompiler(['alert' => TestAlertComponent::class]))->compileTags('<div><x-alert type="" limit=\'\' @click="" required /></div>');

        $this->assertSame("<div> @component('Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent', [])
<?php \$component->withAttributes(['type' => '','limit' => '','@click' => '','required' => true]); ?>
@endcomponentClass</div>", trim($result));
    }

    public function testDataCamelCasing()
    {
        $result = (new ComponentTagCompiler(['profile' => TestProfileComponent::class]))->compileTags('<x-profile user-id="1"></x-profile>');

        $this->assertSame("@component('Bladezero\Tests\Illuminate\View\Blade\TestProfileComponent', ['userId' => '1'])
<?php \$component->withAttributes([]); ?> @endcomponentClass", trim($result));
    }

    public function testColonNestedComponentParsing()
    {
        $result = (new ComponentTagCompiler(['foo:alert' => TestAlertComponent::class]))->compileTags('<x-foo:alert></x-foo:alert>');

        $this->assertSame("@component('Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent', [])
<?php \$component->withAttributes([]); ?> @endcomponentClass", trim($result));
    }

    public function testColonStartingNestedComponentParsing()
    {
        $result = (new ComponentTagCompiler(['foo:alert' => TestAlertComponent::class]))->compileTags('<x:foo:alert></x-foo:alert>');

        $this->assertSame("@component('Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent', [])
<?php \$component->withAttributes([]); ?> @endcomponentClass", trim($result));
    }

    public function testSelfClosingComponentsCanBeCompiled()
    {
        $result = (new ComponentTagCompiler(['alert' => TestAlertComponent::class]))->compileTags('<div><x-alert/></div>');

        $this->assertSame("<div> @component('Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent', [])
<?php \$component->withAttributes([]); ?>
@endcomponentClass</div>", trim($result));
    }

    public function testClassNamesCanBeGuessed()
    {
        $container = new Container;
        $container->instance(Application::class, $app = Mockery::mock(Application::class));
        $app->shouldReceive('getNamespace')->andReturn('App\\');
        Container::setInstance($container);

        $result = (new ComponentTagCompiler([]))->guessClassName('alert');

        $this->assertSame("App\View\Components\Alert", trim($result));

        Container::setInstance(null);
    }

    public function testClassNamesCanBeGuessedWithNamespaces()
    {
        $container = new Container;
        $container->instance(Application::class, $app = Mockery::mock(Application::class));
        $app->shouldReceive('getNamespace')->andReturn('App\\');
        Container::setInstance($container);

        $result = (new ComponentTagCompiler([]))->guessClassName('base.alert');

        $this->assertSame("App\View\Components\Base\Alert", trim($result));

        Container::setInstance(null);
    }

    public function testComponentsCanBeCompiledWithHyphenAttributes()
    {
        $result = (new ComponentTagCompiler(['alert' => TestAlertComponent::class]))->compileTags('<x-alert class="bar" wire:model="foo" x-on:click="bar" @click="baz" />');

        $this->assertSame("@component('Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent', [])
<?php \$component->withAttributes(['class' => 'bar','wire:model' => 'foo','x-on:click' => 'bar','@click' => 'baz']); ?>
@endcomponentClass", trim($result));
    }

    public function testSelfClosingComponentsCanBeCompiledWithDataAndAttributes()
    {
        $result = (new ComponentTagCompiler(['alert' => TestAlertComponent::class]))->compileTags('<x-alert title="foo" class="bar" wire:model="foo" />');

        $this->assertSame("@component('Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent', ['title' => 'foo'])
<?php \$component->withAttributes(['class' => 'bar','wire:model' => 'foo']); ?>
@endcomponentClass", trim($result));
    }

    public function testSelfClosingComponentsCanBeCompiledWithBoundData()
    {
        $result = (new ComponentTagCompiler(['alert' => TestAlertComponent::class]))->compileTags('<x-alert :title="$title" class="bar" />');

        $this->assertSame("@component('Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent', ['title' => \$title])
<?php \$component->withAttributes(['class' => 'bar']); ?>
@endcomponentClass", trim($result));
    }

    public function testPairedComponentTags()
    {
        $result = (new ComponentTagCompiler(['alert' => TestAlertComponent::class]))->compileTags('<x-alert>
</x-alert>');

        $this->assertSame("@component('Bladezero\Tests\Illuminate\View\Blade\TestAlertComponent', [])
<?php \$component->withAttributes([]); ?>
 @endcomponentClass", trim($result));
    }

    public function testClasslessComponents()
    {
        $container = new Container;
        $container->instance(Application::class, $app = Mockery::mock(Application::class));
        $container->instance(Factory::class, $factory = Mockery::mock(Factory::class));
        $app->shouldReceive('getNamespace')->andReturn('App\\');
        $factory->shouldReceive('exists')->andReturn(true);
        Container::setInstance($container);

        $result = (new ComponentTagCompiler([]))->compileTags('<x-anonymous-component name="Taylor" :age="31" wire:model="foo" />');

        $this->assertSame("@component('Bladezero\View\AnonymousComponent', ['view' => 'components.anonymous-component','data' => ['name' => 'Taylor','age' => 31,'wire:model' => 'foo']])
<?php \$component->withAttributes(['name' => 'Taylor','age' => 31,'wire:model' => 'foo']); ?>
@endcomponentClass", trim($result));
    }
}

class TestAlertComponent extends Component
{
    public $title;

    public function __construct($title = 'foo', $userId = 1)
    {
        $this->title = $title;
    }

    public function render()
    {
        return 'alert';
    }
}

class TestProfileComponent extends Component
{
    public $userId;

    public function __construct($userId = 'foo')
    {
        $this->userId = $userId;
    }

    public function render()
    {
        return 'profile';
    }
}
