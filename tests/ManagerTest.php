<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Stuck\Template\Manager;

final class ManagerTest extends TestCase
{
    /** @var Manager */
    private $manager;

    public function setup(): void
    {
        $this->manager = new Manager(__DIR__ . '/Fixtures/templates');
    }

    public function testManager()
    {
        $output = $this->manager->render('layout');
        $output2 = $this->manager->render('layout.php');
        $output3 = $this->manager->getTemplate('layout')->render();

        $this->assertEquals($output, $output2);
        $this->assertEquals($output, $output3);
        $this->assertStringContainsString('Content from Layout', $output);
    }

    public function testGlobalsAndCallbackUsage()
    {
        $this->manager->setGlobals(array('foo' => 'bar'));
        $this->manager->addGlobal('foo', 'FooBarBaz');
        $this->manager->addCallback('foo', function() { return 'bar'; });
        $this->manager->addCallback('bar', function() { return 'baz'; }, true);

        $this->assertStringStartsWith('FooBarBaz', $this->manager->render('parts.consume-foo'));
        $this->assertStringStartsWith('bar', $this->manager->render('parts.call-foo'));
        $this->assertStringStartsWith('baz', $this->manager->render('parts.call-bar'));
        $this->assertStringStartsWith('"foo"', $this->manager->render('parts.chain'));
        $this->assertStringContainsString('&lt;b&gt;Foo&lt;/b&gt;', $this->manager->render('parts.escaping'));
        $this->assertStringContainsString('&lt;b&gt;Bar&lt;/b&gt;', $this->manager->render('parts.escaping'));
        $this->assertStringContainsString('&lt;b&gt;Foo&lt;/b&gt;', $this->manager->render('parts.escape-all'));
    }

    public function testTemplate()
    {
        $this->assertStringContainsString('Layout Title', $this->manager->render('layout'));
        $this->assertStringContainsString('Content Title', $this->manager->render('content'));
        $this->assertStringContainsString('Body from content', $this->manager->render('content'));
        $this->assertStringContainsString('Override by Subcontent', $this->manager->render('subcontent'));
        $this->assertStringContainsString('Subcontent Title | Content Title', $this->manager->render('subcontent'));
        $this->assertStringNotContainsString('Body from content', $this->manager->render('subcontent'));
        $this->assertStringStartsWith('Included text', $this->manager->render('parts.call-load'));
    }

    public function testUnknownTemplate()
    {
        $this->expectExceptionMessage("Template not found: 'foo'");

        $this->manager->render('foo');
    }

    public function testUnknownCallback()
    {
        $this->expectExceptionMessage("Unable to proxy call method: 'unknown'");

        $this->manager->unknown();
    }

    public function testCallParentNotInContext()
    {
        $this->expectExceptionMessage("No parent defined");

        $this->manager->render('parts.call-parent-without-parent');
    }

    public function testCallParentNotInSection()
    {
        $this->expectExceptionMessage("No section defined");

        $this->manager->render('parts.call-parent-without-section');
    }

    public function testCallEndSectionWithoutStarting()
    {
        $this->expectExceptionMessage("No section defined");

        $this->manager->render('parts.call-section-without-section');
    }
}