<?php

require_once __DIR__.'/../CSV/File.php';

class FileTest extends PHPUnit_Framework_TestCase
{
    private $temporaryFiles = array();

    private $sampleRows = array(
        array('1', 'david', '1979'),
        array('2', 'barry alan', '1989'),
        array('3', 'terry', '1980'),
        array('4', 'sam', '1969'),
    );

    private function createTemporaryFilePath()
    {
        $template = '/tmp/csv-test-%s.csv';
        $trace = debug_backtrace();
        $testMethod = $trace[1]['function'];
        $filePath = sprintf($template, $testMethod);
        $this->temporaryFiles[] = $filePath;
        return $filePath;
    }

    public function tearDown()
    {
        foreach ($this->temporaryFiles as $filePath) {
            unlink($filePath);
        }
    }

    public function testWritingDataCreatesAFile()
    {
        $filePath = $this->createTemporaryFilePath();
        $file = new CSV_File($filePath);
        $file->write($this->sampleRows[0]);
        $this->assertTrue(file_exists($filePath));
    }

    public function testWritingDataCanBeReadBack()
    {
        $filePath = $this->createTemporaryFilePath();
        $file = new CSV_File($filePath);
        $file->write($this->sampleRows[0]);

        $newFile = new CSV_File($filePath);
        $this->assertSame(1, $newFile->getNumLines());
    }

    public function testMultilineWrite()
    {
        $filePath = $this->createTemporaryFilePath();
        $file = new CSV_File($filePath);
        $file->writeAll($this->sampleRows);
        $this->assertSame(count($this->sampleRows), $file->getNumLines());
    }

    public function testWrittenDataCanBeReadThroughIterator()
    {
        $filePath = $this->createTemporaryFilePath();
        $file = new CSV_File($filePath);
        $data = $this->sampleRows[0];
        $file->write($data);

        $newFile = new CSV_File($filePath);
        foreach ($newFile as $index => $row) {
            if ($index == 0) $this->assertSame($data, $row);
        }
    }

    public function testGetNamedColumnsBack()
    {
        $filePath = $this->createTemporaryFilePath();
        $file = new CSV_File($filePath);
        $file->setColumnNames(array('id', 'name', 'year'));
        $file->write($this->sampleRows[0]);
        foreach ($file as $row) {
            $this->assertEquals($this->sampleRows[0][0], $row['id']);
        }
    }
}
