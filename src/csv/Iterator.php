<?php

namespace codeinthehole\csv;

/**
 * Simple iterator for a CSV file
 */
class Iterator extends \SplFileObject
{
    protected $names;

    /**
     * @param string $pathToFile
     * @param string $delimiter
     * @param string $fieldEnclosure
     * @param string $escapeChar
     */
    public function __construct($pathToFile, $delimiter=",", $fieldEnclosure='"', $escapeChar='\\')
    {
        parent::__construct($pathToFile, 'r');
        $this->setFlags(\SplFileObject::READ_CSV);
        $this->setCsvControl($delimiter, $fieldEnclosure, $escapeChar);
    }

    /**
     * @param array $names
     * @return CSV_Iterator
     */
    public function setColumnNames(array $names)
    {
        $this->names = $names;
        return $this;
    }

    public function current()
    {
        $row = parent::current();
        if ($this->names) {
            if (count($row) != count($this->names)) {
                return null;
            } else {
                $row = array_combine($this->names, $row);
            }
        }
        return $row;
    }

    public function valid()
    {
        $current = $this->current();
        if ($this->names) {
            return count($current) == count($this->names);
        }
        return parent::valid();
    }
}
