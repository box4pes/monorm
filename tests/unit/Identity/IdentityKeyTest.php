<?php
declare(strict_types=1);

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use PHPUnit\Framework\TestCase;

use Monorm\Identity\IdentityAttribute;

use Monorm\Identity\IdentityKey;
use Monorm\Identity\IdentityKeyInterface;

use Monorm\Identity\Exception\IdentityKeyIsGeneratedException;
use Monorm\Identity\Exception\IdentityKeyFieldsMismatchException;
use Monorm\Identity\Exception\IdentityKeyNoKeyhashException;

/**
 * Description of IdentityKeyTest
 *
 * @author pes2704
 */
class IdentityKeyTest extends TestCase {

    public static function mock() {
    }

    public static function setUpBeforeClass(): void {
    }

    protected function setUp(): void {

    }

    public function testConstruct() {
        $attribute = new IdentityAttribute(['a']);
        $this->assertInstanceOf(IdentityKey::class, new IdentityKey($attribute));
        $this->assertInstanceOf(IdentityKeyInterface::class, new IdentityKey($attribute));
        $this->assertInstanceOf(IdentityKey::class, new IdentityKey($attribute, false));
        $this->assertInstanceOf(IdentityKey::class, new IdentityKey($attribute, true));
    }

    public function testGetAttribute() {
        $attribute = new IdentityAttribute(['a']);
        $key = new IdentityKey($attribute);
        $this->assertEquals($attribute, $key->getAttribute());
    }

    public function testSetKeyHash() {
        $attributeFields = ['a', 'b'];
        $keyHash = ['a'=>1, 'b'=>2];
        $attribute = new IdentityAttribute($attributeFields);
        $key = new IdentityKey($attribute);
        $key->setKeyHash($keyHash);
        $this->assertEquals($keyHash, $key->getKeyHash());

    }

    public function testIsGenerated() {
        $attributeFields = ['a', 'b'];
        $keyHash = ['a'=>1, 'b'=>2];
        $attribute = new IdentityAttribute($attributeFields);
        $key1 = new IdentityKey($attribute, true);
        $this->assertTrue($key1->isGenerated());
        $key2 = new IdentityKey($attribute);
        $this->assertFalse($key2->isGenerated());
    }

    public function testIdentityKeyIsGeneratedException() {
        $attributeFields = ['a', 'b'];
        $keyHash = ['a'=>1, 'b'=>2];
        $attribute = new IdentityAttribute($attributeFields);
        $key = new IdentityKey($attribute, true);
        $this->expectException(IdentityKeyIsGeneratedException::class);
        $key->setKeyHash($keyHash);
    }

    public function testIdentityKeyFieldsMismatchException() {
        $attributeFields = ['a', 'b'];
        $keyHash = ['a'=>1, 'q'=>2];
        $attribute = new IdentityAttribute($attributeFields);
        $key = new IdentityKey($attribute);
        $this->expectException(IdentityKeyFieldsMismatchException::class);
        $key->setKeyHash($keyHash);
    }

    public function testGetKeyHash() {
        $attributeFields = ['a', 'b'];
        $keyHash = ['a'=>1, 'b'=>2];
        $attribute = new IdentityAttribute($attributeFields);
        $key = new IdentityKey($attribute);
        $key->setKeyHash($keyHash);
        $this->assertEquals($keyHash, $key->getKeyHash());
    }

    public function testIdentityKeyNoKeyhashException() {
        $attributeFields = ['a', 'b'];
        $keyHash = ['a'=>1, 'b'=>2];
        $attribute = new IdentityAttribute($attributeFields);
        $key = new IdentityKey($attribute);
        $this->expectException(IdentityKeyNoKeyhashException::class);
        $key->getKeyHash();
    }

    public function testIsEqual() {
        $attributeFields1 = ['a', 'b'];
        $keyHash1 = ['a'=>1, 'b'=>2];
        $attribute1 = new IdentityAttribute($attributeFields1);
        $key1 = new IdentityKey($attribute1);
        $key1->setKeyHash($keyHash1);

        $attributeFields2 = ['a', 'b'];
        $keyHash2 = ['a'=>1, 'b'=>2];
        $attribute2 = new IdentityAttribute($attributeFields2);
        $key2 = new IdentityKey($attribute2);
        $key2->setKeyHash($keyHash2);

        $this->assertTrue($key1->isEqual($key2));

        $keyHash3 = ['a'=>1, 'b'=>3];
        $key2->setKeyHash($keyHash3);
        $this->assertFalse($key1->isEqual($key2));
    }


    public function testSerialize() {
        $fields = ['a', 'b', 'c'];
        $attribute = new IdentityAttribute($fields);
        $key = new IdentityKey($attribute);
        $keyHash = ['a'=>1, 'b'=>2, 'c'=>'kuk'];
        $key->setKeyHash($keyHash);
        $serialized = serialize($key);

        /** @var IdentityKey $unserKey */
        $unserKey = unserialize($serialized);
        $this->assertInstanceOf(IdentityKey::class, $unserKey);
        $this->assertInstanceOf(IdentityAttribute::class, $unserKey->getAttribute());
        $this->assertEquals($fields, $unserKey->getAttribute()->getFields());
        $this->assertEquals($keyHash, $unserKey->getKeyHash());
    }



}