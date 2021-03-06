<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator\File;

use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\File\ExcludeMimeType;
use PHPUnit\Framework\TestCase;

/**
 * ExcludeMimeType testbed
 *
 * @group      Laminas_Validator
 */
class ExcludeMimeTypeTest extends TestCase
{
    /**
     * @return array
     */
    public function basicBehaviorDataProvider()
    {
        $testFile = __DIR__ . '/_files/picture.jpg';
        $fileUpload = [
            'tmp_name' => $testFile,
            'name'     => basename($testFile),
            'size'     => 200,
            'error'    => 0,
            'type'     => 'image/jpeg',
        ];

        $falseTypeMessage = [ExcludeMimeType::FALSE_TYPE => "File has an incorrect mimetype of 'image/jpeg'"];

        return [
            //    Options, isValid Param, Expected value, messages
            ['image/gif',                 $fileUpload, true,  []],
            ['image',                     $fileUpload, false, $falseTypeMessage],
            ['test/notype',               $fileUpload, true,  []],
            ['image/gif, image/jpeg',     $fileUpload, false, $falseTypeMessage],
            [['image/vasa', 'image/gif'], $fileUpload, true,  []],
            [['image/gif', 'jpeg'],       $fileUpload, false, $falseTypeMessage],
            [['image/gif', 'gif'],        $fileUpload, true,  []],
        ];
    }

    /**
     * Ensures that the validator follows expected behavior
     *
     * @dataProvider basicBehaviorDataProvider
     *
     * @param string|array $options
     * @param array $isValidParam
     * @param bool $expected
     * @param array $messages
     *
     * @return void
     */
    public function testBasic($options, array $isValidParam, $expected, array $messages): void
    {
        $validator = new ExcludeMimeType($options);
        $validator->enableHeaderCheck();
        $this->assertEquals($expected, $validator->isValid($isValidParam));
        $this->assertEquals($messages, $validator->getMessages());
    }

    /**
     * Ensures that the validator follows expected behavior for legacy Laminas\Transfer API
     *
     * @dataProvider basicBehaviorDataProvider
     * @return void
     */
    public function testLegacy($options, $isValidParam, $expected)
    {
        if (is_array($isValidParam)) {
            $validator = new ExcludeMimeType($options);
            $validator->enableHeaderCheck();
            $this->assertEquals($expected, $validator->isValid($isValidParam['tmp_name'], $isValidParam));
        }
    }

    /**
     * Ensures that getMimeType() returns expected value
     *
     * @return void
     */
    public function testGetMimeType()
    {
        $validator = new ExcludeMimeType('image/gif');
        $this->assertEquals('image/gif', $validator->getMimeType());

        $validator = new ExcludeMimeType(['image/gif', 'video', 'text/test']);
        $this->assertEquals('image/gif,video,text/test', $validator->getMimeType());

        $validator = new ExcludeMimeType(['image/gif', 'video', 'text/test']);
        $this->assertEquals(['image/gif', 'video', 'text/test'], $validator->getMimeType(true));
    }

    /**
     * Ensures that setMimeType() returns expected value
     *
     * @return void
     */
    public function testSetMimeType()
    {
        $validator = new ExcludeMimeType('image/gif');
        $validator->setMimeType('image/jpeg');
        $this->assertEquals('image/jpeg', $validator->getMimeType());
        $this->assertEquals(['image/jpeg'], $validator->getMimeType(true));

        $validator->setMimeType('image/gif, text/test');
        $this->assertEquals('image/gif,text/test', $validator->getMimeType());
        $this->assertEquals(['image/gif', 'text/test'], $validator->getMimeType(true));

        $validator->setMimeType(['video/mpeg', 'gif']);
        $this->assertEquals('video/mpeg,gif', $validator->getMimeType());
        $this->assertEquals(['video/mpeg', 'gif'], $validator->getMimeType(true));
    }

    /**
     * Ensures that addMimeType() returns expected value
     *
     * @return void
     */
    public function testAddMimeType()
    {
        $validator = new ExcludeMimeType('image/gif');
        $validator->addMimeType('text');
        $this->assertEquals('image/gif,text', $validator->getMimeType());
        $this->assertEquals(['image/gif', 'text'], $validator->getMimeType(true));

        $validator->addMimeType('jpg, to');
        $this->assertEquals('image/gif,text,jpg,to', $validator->getMimeType());
        $this->assertEquals(['image/gif', 'text', 'jpg', 'to'], $validator->getMimeType(true));

        $validator->addMimeType(['zip', 'ti']);
        $this->assertEquals('image/gif,text,jpg,to,zip,ti', $validator->getMimeType());
        $this->assertEquals(['image/gif', 'text', 'jpg', 'to', 'zip', 'ti'], $validator->getMimeType(true));

        $validator->addMimeType('');
        $this->assertEquals('image/gif,text,jpg,to,zip,ti', $validator->getMimeType());
        $this->assertEquals(['image/gif', 'text', 'jpg', 'to', 'zip', 'ti'], $validator->getMimeType(true));
    }

    public function testEmptyFileShouldReturnFalseAndDisplayNotFoundMessage(): void
    {
        $validator = new ExcludeMimeType();

        $this->assertFalse($validator->isValid(''));
        $this->assertArrayHasKey(ExcludeMimeType::NOT_READABLE, $validator->getMessages());
        $this->assertNotEmpty($validator->getMessages()[ExcludeMimeType::NOT_READABLE]);
    }

    public function testEmptyArrayFileShouldReturnFalseAdnDisplayNotFoundMessage(): void
    {
        $validator = new ExcludeMimeType();

        $filesArray = [
            'name'      => '',
            'size'      => 0,
            'tmp_name'  => '',
            'error'     => UPLOAD_ERR_NO_FILE,
            'type'      => '',
        ];

        $this->assertFalse($validator->isValid($filesArray));
        $this->assertArrayHasKey(ExcludeMimeType::NOT_READABLE, $validator->getMessages());
        $this->assertNotEmpty($validator->getMessages()[ExcludeMimeType::NOT_READABLE]);
    }

    public function testIsValidRaisesExceptionWithArrayNotInFilesFormat(): void
    {
        $validator = new ExcludeMimeType('image\gif');
        $value     = ['foo' => 'bar'];
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Value array must be in $_FILES format');
        $validator->isValid($value);
    }
}
