===============
PHP CSV objects
===============

Just a simple set of objects that making dealing with CSV files easier.

Sample usage
------------

Reading data out of a CSV file::

    $pathToFile = '/path/to/file.csv';
    $file = new CSV_File($pathToFile);
    $file->setFieldDelimiter('|'); // optional 
    foreach ($file as $row) {
        do_something_with($row['name'], $row['age']);
    }

Writing::

    $pathToFile = '/path/to/file.csv';
    $file = new CSV_File($pathToFile);
    $file->write('col1', 'col2', 'col3');
    
Process a raw CSV into a new one::

    $pathToSourceFile = '/path/to/rawfile.csv';
    $inFile = new CSV_File($pathToFile);
    $inFile->setColumnNames(array('name', 'age');

    $pathToDesinationFile = '/path/to/file.csv';
    $outFile = new CSV_File($pathToDestinationFile);

    foreach ($inFile as $inRow) {
        if ($inRow['age'] < 21) {
            continue;
        }
        $outRow = array(
            strtoupper($inRow['name']),
            (int)$inRow['age']
        );
        $outFile->write($outRow);
    }
    $inFile->delete();

Features
--------

* Set names of columns for more readable code
* Uses a ``SplFileObject`` as an iterator, which can be subclassed or overridden for custom behaviour.

To come
-------

* A loader class for working with MySQL's ``LOAD DATA INFILE ...`` command.
