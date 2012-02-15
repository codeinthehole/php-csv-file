<?php

require_once __DIR__.'/Iterator.php';

/**
 * CSV file object
 */
class CSV_File implements IteratorAggregate
{

	/**
	 * Default CSV value delimiter.
	 *
	 * @var string
	 */
	const DEFAULT_DELIMITER = ",";

	/**
	 * Default CSV string enclosure.
	 *
	 * @var string
	 */
	const DEFAULT_ENCLOSURE = '"';

    /**
     * A full path to CSV file that is being written
     *
     * @var string
     */
    private $pathToFile;

    /**
     * File pointer created by fopen function
     *
     * @var resource
     */
    protected $filePointer;

    /**
     * @var string
     */
    private $fieldDelimiter = self::DEFAULT_DELIMITER;

    /**
     * @var string
     */
    private $fieldEnclosure = self::DEFAULT_ENCLOSURE;

    /**
     * @var string
     */
    private $lineTerminator = PHP_EOL;

    /**
     * @var string
     */
    private $iteratorClass = 'CSV_Iterator';

    /**
     * @var boolean
     */
    private $append = false;

    private $columnNames;

    /**
     * Constructor only takes a full CSV file path and inits class properties
     *
     * @param string $pathToFile
     * @param boolean $append If true then we will not truncate the file but allow appending to an existing file.
     */
    public function __construct($pathToFile, $append=false)
    {
        ini_set('auto_detect_line_endings', true);
        $this->pathToFile = $pathToFile;
        $this->append = $append;
    }

    /**
     * @param string $delimiter
     * @return CSV_File
     */
    public function setFieldDelimiter($delimiter)
    {
        $this->fieldDelimiter = $delimiter;
        return $this;
    }

    /**
     * @param string $enclosure
     * @return CSV_File
     */
    public function setFieldEnclosure($enclosure)
    {
        $this->fieldEnclosure = $enclosure;
        return $this;
    }

    /**
     * @param string $terminator
     * @return CSV_File
     */
    public function setLineTerminator($terminator)
    {
        $this->lineTerminator = $terminator;
        return $this;
    }

    /**
     * @param array $names
     * @return CSV_File
     */
    public function setColumnNames(array $names)
    {
        $this->columnNames = $names;
    }

    /**
     * Set a custom itertor class to use when looping over the data.
     * This should be a subclass of CSV_FileIterator
     *
     * @param string $className
     * @return CSV_File
     */
    public function setIteratorClass($className)
    {
        $this->iteratorClass = $className;
        return $this;
    }

    /**
     * Instantiate class properties and open file resource
     *
     * @return void
     */
    private function init()
    {
        $mode = 'w';
        if ($this->append) {
            $mode = 'a';
        }
        $this->filePointer = fopen($this->pathToFile, $mode);
        if (false === $this->filePointer) {
            throw new RuntimeException("Unable to open file $this->pathToFile");
        }
    }

    /**
     * @return void
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getPath();
    }

    // ============== //
    // PUBLIC METHODS //
    // ============== //

    /**
     * Output contents of the array into a single line in CSV file, with separator specified in class constant
     *
     * @param array $contentArray
     * @return mixed
     */
    public function write(array $contentArray)
    {
        if (!is_resource($this->filePointer)) $this->init();
        if ($contentArray) {
            $writeOk = fputcsv($this->filePointer, array_values($contentArray), $this->fieldDelimiter, $this->fieldEnclosure);
            if (false === $writeOk) {
                throw new Exception("Unable to write CSV data to file $this->pathToFile");
            }
        }
        return $this;
    }

    /**
     * @param array $contentMultiArray
     * @return CSV_File
     */
    public function writeAll(array $contentMultiArray)
    {
        foreach ($contentMultiArray as $contentArray) {
            $this->write($contentArray);
        }
        return $this;
    }

    /**
     * @return boolean
     */
    public function exists()
    {
        return file_exists($this->pathToFile);
    }

    /**
     * @return int
     */
    public function getFileSize()
    {
        return $this->exists() ? filesize($this->pathToFile) : null;
    }

    /**
     * Close file resource (handle) using fclose
     *
     * @return nothing
     */
    public function close()
    {
        if (is_resource($this->filePointer)) fclose($this->filePointer);
        return $this;
    }

    /**
     * Close & delete CSV file
     *
     * @return nothing
     */
    public function delete()
    {
        $this->close();
        if ($this->exists()) unlink($this->pathToFile);
    }

    /**
     * Get CSV file path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->pathToFile;
    }

    /**
     * @return string
     */
    public function getFieldDelimiter()
    {
        return $this->fieldDelimiter;
    }

    /**
     * @return string
     */
    public function getFieldEnclosure()
    {
        return $this->fieldEnclosure;
    }

    /**
     * @return string
     */
    public function getLineTerminator()
    {
        return $this->lineTerminator;
    }

    /**
     * @return CSV_Iterator
     */
    public function getIterator()
    {
        if (!$this->exists()) {
            throw new RuntimeException("The file $this->pathToFile does not exist");
        }
        $class = new $this->iteratorClass($this->pathToFile, $this->fieldDelimiter, $this->fieldEnclosure);
        if ($this->columnNames) {
            $class->setColumnNames($this->columnNames);
        }
        return $class;
    }

    /**
     * @return int
     */
    public function getNumLines()
    {
        return count(file($this->pathToFile));
    }
}