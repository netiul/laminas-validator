<?php

/**
 * @see       https://github.com/laminas/laminas-validator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-validator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-validator/blob/master/LICENSE.md New BSD License
 */

namespace LaminasTest\Validator\File;

use LaminasTest\Validator\File\TestAsset\FileInformation;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

class FileInformationTraitTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy|StreamInterface */
    public $stream;

    /** @var ObjectProphecy */
    public $upload;

    protected function setUp() : void
    {
        $this->stream = $this->prophesize(StreamInterface::class);
        $this->upload = $this->prophesize(UploadedFileInterface::class);
    }

    public function testLegacyFileInfoBasic(): void
    {
        $testFile = __DIR__ . '/_files/testsize.mo';
        $basename = basename($testFile);
        $file = [
          'name'     => $basename,
          'tmp_name' => $testFile,
        ];

        $fileInformation = new FileInformation();
        $fileInfo = $fileInformation->checkFileInformation(
            $basename,
            $file
        );

        $this->assertEquals($fileInfo, [
          'filename' => $file['name'],
          'file'     => $file['tmp_name'],
        ]);
    }

    public function testLegacyFileInfoWithFiletype(): void
    {
        $testFile = __DIR__ . '/_files/testsize.mo';
        $basename = basename($testFile);
        $file = [
          'name'     => $basename,
          'tmp_name' => $testFile,
          'type' => 'mo',
        ];

        $fileInformation = new FileInformation();
        $fileInfo = $fileInformation->checkFileInformation(
            $basename,
            $file,
            true
        );

        $this->assertEquals($fileInfo, [
          'filename' => $file['name'],
          'file'     => $file['tmp_name'],
          'filetype' => $file['type'],
        ]);
    }

    public function testLegacyFileInfoWithBasename(): void
    {
        $testFile = __DIR__ . '/_files/testsize.mo';
        $basename = basename($testFile);
        $file = [
          'name'     => $basename,
          'tmp_name' => $testFile,
        ];

        $fileInformation = new FileInformation();
        $fileInfo = $fileInformation->checkFileInformation(
            $basename,
            $file,
            false,
            true
        );

        $this->assertEquals($fileInfo, [
          'filename' => $file['name'],
          'file'     => $file['tmp_name'],
          'basename' => basename($file['tmp_name']),
        ]);
    }

    public function testSapiFileInfoBasic(): void
    {
        $testFile = __DIR__ . '/_files/testsize.mo';
        $file = [
          'name'     => basename($testFile),
          'tmp_name' => $testFile,
        ];

        $fileInformation = new FileInformation();
        $fileInfo = $fileInformation->checkFileInformation(
            $file
        );

        $this->assertEquals($fileInfo, [
          'filename' => $file['name'],
          'file'     => $file['tmp_name'],
        ]);
    }

    public function testSapiFileInfoWithFiletype(): void
    {
        $testFile = __DIR__ . '/_files/testsize.mo';
        $file = [
          'name'     => basename($testFile),
          'tmp_name' => $testFile,
          'type'     => 'mo',
        ];

        $fileInformation = new FileInformation();
        $fileInfo = $fileInformation->checkFileInformation(
            $file,
            null,
            true
        );

        $this->assertEquals($fileInfo, [
          'filename' => $file['name'],
          'file'     => $file['tmp_name'],
          'filetype' => $file['type'],
        ]);
    }

    public function testSapiFileInfoWithBasename(): void
    {
        $testFile = __DIR__ . '/_files/testsize.mo';
        $file = [
          'name'     => basename($testFile),
          'tmp_name' => $testFile,
        ];

        $fileInformation = new FileInformation();
        $fileInfo = $fileInformation->checkFileInformation(
            $file,
            null,
            false,
            true
        );

        $this->assertEquals($fileInfo, [
          'filename' => $file['name'],
          'file'     => $file['tmp_name'],
          'basename' => basename($file['tmp_name']),
        ]);
    }

    public function testPsr7FileInfoBasic(): void
    {
        $testFile = __DIR__ . '/_files/testsize.mo';

        $this->stream->getMetadata('uri')->willReturn($testFile);
        $this->upload->getClientFilename()->willReturn(basename($testFile));
        $this->upload->getClientMediaType()->willReturn(mime_content_type($testFile));
        $this->upload->getStream()->willReturn($this->stream->reveal());

        $fileInformation = new FileInformation();
        $fileInfo = $fileInformation->checkFileInformation(
            $this->upload->reveal()
        );

        $this->assertEquals($fileInfo, [
          'filename' => basename($testFile),
          'file'     => $testFile,
        ]);
    }

    public function testPsr7FileInfoBasicWithFiletype(): void
    {
        $testFile = __DIR__ . '/_files/testsize.mo';

        $this->stream->getMetadata('uri')->willReturn($testFile);
        $this->upload->getClientFilename()->willReturn(basename($testFile));
        $this->upload->getClientMediaType()->willReturn(mime_content_type($testFile));
        $this->upload->getStream()->willReturn($this->stream->reveal());

        $fileInformation = new FileInformation();
        $fileInfo = $fileInformation->checkFileInformation(
            $this->upload->reveal(),
            null,
            true
        );

        $this->assertEquals($fileInfo, [
          'filename' => basename($testFile),
          'file'     => $testFile,
          'filetype' => mime_content_type($testFile),
        ]);
    }

    public function testPsr7FileInfoBasicWithBasename(): void
    {
        $testFile = __DIR__ . '/_files/testsize.mo';

        $this->stream->getMetadata('uri')->willReturn($testFile);
        $this->upload->getClientFilename()->willReturn(basename($testFile));
        $this->upload->getClientMediaType()->willReturn(mime_content_type($testFile));
        $this->upload->getStream()->willReturn($this->stream->reveal());

        $fileInformation = new FileInformation();
        $fileInfo = $fileInformation->checkFileInformation(
            $this->upload->reveal(),
            null,
            false,
            true
        );

        $this->assertEquals($fileInfo, [
          'filename' => basename($testFile),
          'file'     => $testFile,
          'basename' => basename($testFile),
        ]);
    }

    public function testFileBasedFileInfoBasic(): void
    {
        $testFile = __DIR__ . '/_files/testsize.mo';

        $fileInformation = new FileInformation();
        $fileInfo = $fileInformation->checkFileInformation(
            $testFile
        );

        $this->assertEquals($fileInfo, [
          'filename' => basename($testFile),
          'file'     => $testFile,
        ]);
    }

    public function testFileBasedFileInfoBasicWithFiletype(): void
    {
        $testFile = __DIR__ . '/_files/testsize.mo';

        $fileInformation = new FileInformation();
        $fileInfo = $fileInformation->checkFileInformation(
            $testFile,
            null,
            true
        );

        $this->assertEquals($fileInfo, [
          'filename' => basename($testFile),
          'file'     => $testFile,
          'filetype' => null,
        ]);
    }

    public function testFileBasedFileInfoBasicWithBasename(): void
    {
        $testFile = __DIR__ . '/_files/testsize.mo';

        $fileInformation = new FileInformation();
        $fileInfo = $fileInformation->checkFileInformation(
            $testFile,
            null,
            false,
            true
        );

        $this->assertEquals($fileInfo, [
          'filename' => basename($testFile),
          'file'     => $testFile,
          'basename' => basename($testFile),
        ]);
    }
}
