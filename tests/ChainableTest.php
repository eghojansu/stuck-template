<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Stuck\Template\Chainable;
use Stuck\Template\Manager;

final class ChainableTest extends TestCase
{
    public function testChainable()
    {
        $manager = new Manager();

        $this->assertEquals(' foo ', (new Chainable($manager, ' foo '))->value);
        $this->assertEquals('foo', (string) (new Chainable($manager, ' foo '))->ltrim()->rtrim());
        $this->assertEquals('', (string) (new Chainable($manager)));
        $this->assertEquals('[1]', (string) (new Chainable($manager, array(1))));
    }

    public function testAccessOtherProperty()
    {
        $this->expectExceptionMessage("Property not exists: 'foo'");

        (new Chainable(new Manager()))->foo;
    }
}