<?php
declare(strict_types=1);

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use PHPUnit\Framework\TestCase;

use Monorm\Identity\IdentityAttributeInterface;
use Monorm\Identity\IdentityAttribute;
use Monorm\Identity\Exception\InvalidAttributeFieldsException;

/**
 * Description of MonormTest
 *
 * @author pes2704
 */
class IdentityAttributeTest extends TestCase {

    public static function mock() {
    }

    public static function setUpBeforeClass(): void {
    }

    protected function setUp(): void {
    }

    public function testConstruct() {
        $this->assertInstanceOf(IdentityAttributeInterface::class, new IdentityAttribute(['a', 'b', 'c']));
        $this->assertInstanceOf(IdentityAttribute::class, new IdentityAttribute(['a', 'b', 'c']));
    }

    public function testInvalidAttributeFieldsException() {
        $this->expectException(InvalidAttributeFieldsException::class);
        $a = new IdentityAttribute([]);
    }

    public function testGetFields() {
        $fields = ['a', 'b', 'c'];
        $attribute = new IdentityAttribute($fields);
        $this->assertEquals($fields, $attribute->getFields());
    }

    public function testIsEqual() {
        $fields = ['a', 'b', 'c'];
        $attribute = new IdentityAttribute($fields);
        $eq = $attribute->isEqual(new IdentityAttribute($fields));
        $this->assertTrue($attribute->isEqual(new IdentityAttribute($fields)));
    }

    public function testSerialize() {
        $fields = ['a', 'b', 'c'];
        $serialized = serialize(new IdentityAttribute($fields));
        $attribute = unserialize($serialized);
        $this->assertInstanceOf(IdentityAttribute::class, $attribute);
        $this->assertEquals($fields, $attribute->getFields());
    }

}