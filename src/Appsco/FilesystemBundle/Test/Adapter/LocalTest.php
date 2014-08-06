<?php
namespace Appsco\FilesystemBundle\Adapter {
    function file_get_contents($k)
    {
        return 'string';
    }
}

namespace Appsco\FilesystemBundle\Test\Adapter {

    use Appsco\FilesystemBundle\Adapter\Local;

    class LocalTest extends \PHPUnit_Framework_TestCase
    {
        /**
         * @var \PHPUnit_Framework_MockObject_MockObject
         */
        private $filesystemMock;

        protected function setUp()
        {
            $this->filesystemMock = $this->getMock('Symfony\Component\Filesystem\Filesystem');
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

            $mock = $this->getMockBuilder('Appsco\FilesystemBundle\Adapter\Local')
                ->setConstructorArgs([$this->filesystemMock, false])
                ->setMethods(['isDirectory'])
                ->getMock();
            $mock->expects($this->once())
                ->method('isDirectory')
                ->with($key)
                ->willReturn(false);

            $mock->getVolume($key);

            $this->assertEquals('string', $mock->read($key));
        }

        /**
         * @test
         */
        public function shouldReturnFalseWhenReadingIfItIsDirectory()
        {
            $key = 'key';
            $mock = $this->getMockBuilder('Appsco\FilesystemBundle\Adapter\Local')
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
                ->will($this->throwException(new \Exception()));
            ;

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
        public function shouldReturnTrueIfFileExists(){
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
        public function shouldReturnFalseIfFileDoesntExists(){
            $this->filesystemMock->expects($this->once())
                ->method('exists')
                ->withAnyParameters()
                ->willReturn(false);

            $local = new Local($this->filesystemMock, false);

            $this->assertFalse($local->exists('dir'));
        }
    }
}