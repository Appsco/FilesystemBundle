<?php

namespace Appsco\FilesystemBundle\Test\Adapter {

    use Appsco\FilesystemBundle\Adapter\Local\Local;
    use Appsco\FilesystemBundle\Model\File;

    class LocalTest extends \PHPUnit_Framework_TestCase
    {
        /**
         * @var \PHPUnit_Framework_MockObject_MockObject
         */
        private $filesystemMock;

        protected function setUp()
        {
            $this->filesystemMock = $this->getMock('Appsco\FilesystemBundle\Adapter\Local\Filesystem');
        }

        /**
         * @test
         */
        public function shouldReturnSelfWhenFolderDoesntExistAndCreateVolumeTrue()
        {
            $this->filesystemMock->expects($this->any())
                ->method('exists')
                ->with($volumeName = 'key')
                ->willReturn(false);

            $this->filesystemMock->expects($this->once())
                ->method('mkdir')
                ->with($volumeName);

            $local = new Local($this->filesystemMock, true);

            $this->assertSame($local, $local->getVolume($volumeName));
        }

        /**
         * @test
         * @expectedException \RuntimeException
         */
        public function shouldThrowErrorWhenFolderDoesntExistAndCreateVolumeFalse()
        {
            $this->filesystemMock->expects($this->any())
                ->method('exists')
                ->with($volumeName = 'key')
                ->willReturn(false);

            $this->filesystemMock->expects($this->never())
                ->method('mkdir')
                ->with($volumeName);

            $local = new Local($this->filesystemMock, false);

            $local->getVolume($volumeName);
        }

        /**
         * @test
         */
        public function shouldReturnSelfIfFolderExists()
        {
            $this->filesystemMock->expects($this->any())
                ->method('exists')
                ->with($volumeName = 'key')
                ->willReturn(true);

            $this->filesystemMock->expects($this->never())
                ->method('mkdir')
                ->with($volumeName);

            $local = new Local($this->filesystemMock, false);

            $this->assertSame($local, $local->getVolume($volumeName));
        }

        /**
         * @test
         */
        public function shouldReturnFileContentWhenReading()
        {

            $key = 'key';

            $this->filesystemMock->expects($this->any())
                ->method('exists')
                ->with($key)
                ->willReturn(true);

            $this->filesystemMock->expects($this->never())
                ->method('mkdir')
                ->with($key);

            $this->filesystemMock->expects($this->once())
              ->method('read')
              ->with("$key/$key")
              ->willReturn($file = new File());

            $mock = $this->getMockBuilder('Appsco\FilesystemBundle\Adapter\Local\Local')
                ->setConstructorArgs([$this->filesystemMock, false])
                ->setMethods(['isDirectory'])
                ->getMock();
            $mock->expects($this->once())
                ->method('isDirectory')
                ->with($key)
                ->willReturn(false);

            $mock->getVolume($key);

            $this->assertSame($file, $mock->read($key));
        }

        /**
         * @test
         */
        public function shouldReturnFalseWhenReadingIfItIsDirectory()
        {
            $key = 'key';
            $mock = $this->getMockBuilder('Appsco\FilesystemBundle\Adapter\Local\Local')
                ->setConstructorArgs([$this->filesystemMock, false])
                ->setMethods(['isDirectory'])
                ->getMock();
            $mock->expects($this->once())
                ->method('isDirectory')
                ->with($key)
                ->willReturn(true);


            $this->assertFalse($mock->read($key));
        }

        /**
         * @test
         */
        public function shouldReturnTrueIfFileIsWritten()
        {
            $this->filesystemMock->expects($this->once())
                ->method('dumpFile')
                ->withAnyParameters();

            $local = new Local($this->filesystemMock, false);

            $this->assertTrue($local->write('key', 'content'));
        }

        /**
         * @test
         */
        public function shouldReturnFalseIfFileCantBeWritten()
        {
            $this->filesystemMock->expects($this->once())
                ->method('dumpFile')
                ->withAnyParameters()
                ->will($this->throwException(new \Exception()));;

            $local = new Local($this->filesystemMock, false);

            $this->assertFalse($local->write('key', 'content'));
        }

        /**
         * @test
         */
        public function shouldReturnTrueIfDirectoryIsCreated()
        {
            $this->filesystemMock->expects($this->once())
                ->method('mkdir')
                ->withAnyParameters()
                ->willReturn(true);

            $local = new Local($this->filesystemMock, false);

            $this->assertTrue($local->mkdir('dir'));
        }

        /**
         * @test
         */
        public function shouldReturnFalseIfDirectoryIsntCreated()
        {
            $this->filesystemMock->expects($this->once())
                ->method('mkdir')
                ->withAnyParameters()
                ->willThrowException(new \Exception());

            $local = new Local($this->filesystemMock, false);

            $this->assertFalse($local->mkdir('dir'));
        }

        /**
         * @test
         */
        public function shouldReturnTrueIfFileExists()
        {
            $this->filesystemMock->expects($this->once())
                ->method('exists')
                ->withAnyParameters()
                ->willReturn(true);

            $local = new Local($this->filesystemMock, false);

            $this->assertTrue($local->exists('dir'));
        }

        /**
         * @test
         */
        public function shouldReturnFalseIfFileDoesntExists()
        {
            $this->filesystemMock->expects($this->once())
                ->method('exists')
                ->withAnyParameters()
                ->willReturn(false);

            $local = new Local($this->filesystemMock, false);

            $this->assertFalse($local->exists('dir'));
        }

        /**
         * @test
         */
        public function shouldReturnListOfKeys()
        {
            $local = new Local($this->filesystemMock, false);

            $this->assertInternalType('array', $local->keys());
        }

        /**
         * @test
         */
        public function shouldReturnTimestamp()
        {
            $key = __FILE__;

            $this->filesystemMock->expects($this->any())
                ->method('exists')
                ->with('')
                ->willReturn(true);

            $this->filesystemMock->expects($this->never())
                ->method('mkdir')
                ->with($key);

            $local = new Local($this->filesystemMock, false);
            $local->getVolume('');

            $this->assertEquals(filemtime(__FILE__), $local->mtime($key));
        }

        /**
         * @test
         */
        public function shouldReturnFalseIfFileDoesntExist()
        {
            $key = __FILE__;

            $this->filesystemMock->expects($this->any())
                ->method('exists')
                ->with('')
                ->willReturn(true);

            $local = new Local($this->filesystemMock, false);
            $local->getVolume('');

            $this->assertFalse($local->mtime($key . rand(0, 100)));
        }

        /**
         * @test
         */
        public function shouldReturnTrueIfFileIsDeleted()
        {
            $this->filesystemMock->expects($this->any())
                ->method('remove')
                ->withAnyParameters()
                ->willReturn(true);

            $local = new Local($this->filesystemMock, false);
            $this->assertTrue($local->delete('string'));
        }

        /**
         * @test
         */
        public function shouldReturnFalseIfFileCantBeDeleted()
        {
            $this->filesystemMock->expects($this->any())
                ->method('remove')
                ->withAnyParameters()
                ->willThrowException(new \Exception());

            $local = new Local($this->filesystemMock, false);
            $this->assertFalse($local->delete('string'));
        }

        /**
         * @test
         */
        public function shouldReturnTrueIfFileIsRenamed()
        {
            $this->filesystemMock->expects($this->any())
                ->method('rename')
                ->withAnyParameters()
                ->willReturn(true);

            $local = new Local($this->filesystemMock, false);
            $this->assertTrue($local->rename('string', 'string'));
        }

        /**
         * @test
         */
        public function shouldReturnFalseIfFileCantBeRenamed()
        {
            $this->filesystemMock->expects($this->any())
                ->method('rename')
                ->withAnyParameters()
                ->willThrowException(new \Exception());

            $local = new Local($this->filesystemMock, false);
            $this->assertFalse($local->rename('string', 'string'));
        }

        /**
         * @test
         */
        public function shouldReturnTrueIfDirectoryExists()
        {
            $key = __DIR__;

            $this->filesystemMock->expects($this->any())
                ->method('exists')
                ->with('')
                ->willReturn(true);

            $local = new Local($this->filesystemMock, false);
            $local->getVolume('');

            $this->assertTrue($local->isDirectory($key));
        }

        /**
         * @test
         */
        public function shouldReturnFalseIfDirectoryDoesntExists()
        {
            $key = __DIR__ . rand(0,100);

            $this->filesystemMock->expects($this->any())
                ->method('exists')
                ->with('')
                ->willReturn(true);

            $local = new Local($this->filesystemMock, false);
            $local->getVolume('');

            $this->assertFalse($local->isDirectory($key));
        }
    }
}