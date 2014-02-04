<?php

namespace codeinthehole\csv;

/**
 * CSV file object
 */
class File implements \IteratorAggregate
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
	 * Delimiter for csv values.
	 *
     * @var string
     */
    private $fieldDelimiter = self::DEFAULT_DELIMITER;

    /**
	 * Delimiters for fields in one csv field.
	 *
     * @var string
     */
    private $fieldEnclosure = self::DEFAULT_ENCLOSURE;

    /**
	 * Delimiter for a dataset.
	 *
     * @var string
     */
    private $lineTerminator = PHP_EOL;

    /**
	 * Determines if the writer shell append to current file.
	 *
     * @var boolean
     */
    private $append = false;

	/**
	 * Stores Iterator instance.
	 *
	 * @var Iterator;
	 */
	private $iterator;

	/**
	 * Stores column names.
	 *
	 * @var array
	 */
    private $columnNames;

    /**
     * Constructor only takes a full CSV file path and inits class properties.
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
     * @return File
     */
    public function setFieldDelimiter($delimiter)
    {
        $this->fieldDelimiter = $delimiter;
        return $this;
    }

    /**
     * @param string $enclosure
     * @return File
     */
    public function setFieldEnclosure($enclosure)
    {
        $this->fieldEnclosure = $enclosure;
        return $this;
    }

    /**
     * @param string $terminator
     * @return File
     */
    public function setLineTerminator($terminator)
    {
        $this->lineTerminator = $terminator;
        return $this;
    }

    /**
     * @param array $names
     * @return File
     */
    public function setColumnNames(array $names)
    {
        $this->columnNames = $names;
    }

    /**
     * Set a custom itertor to use when looping over the data.
     * This should be a subclass of codeinthehole\csv\FileIterator
     *
     * @param codeinthehole\csv\Iterator $iterator
     * @return codeinthehole\csv\File
     */
    public function setIterator($iterator)
    {
        $this->iterator = $iterator;
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
     * @return File
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
     * @return File
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
     * @return Iterator
     */
    public function buildIterator()
    {
        if (!$this->exists()) {
            throw new RuntimeException("The file $this->pathToFile does not exist");
        }
        $iterator = new Iterator($this->pathToFile, $this->fieldDelimiter, $this->fieldEnclosure);
        if ($this->columnNames) {
            $iterator->setColumnNames($this->columnNames);
        }
        return $iterator;
    }

	/**
	 * @return Iterator
	 */
	public function getIterator()
	{
		if (!$this->iterator) {
			$this->iterator = $this->buildIterator();
		}
		return $this->iterator;
	}

    /**
     * @return int
     */
    public function getNumLines()
    {
        return count(file($this->pathToFile));
    }
}