<?php

// it's easier to regenerate classmaps like this
// just add a new directory to the array
$directories = array(
    __DIR__ . '/application',
    __DIR__ . '/library/Zend',
    __DIR__ . '/library/PPN'
);

foreach($directories as $dir) {
    $classmapGenerator = 'php -d memory_limit=-1 ' . __DIR__ . '/bin/classmap_generator.php -l ' . $dir . ' -w';
    system($classmapGenerator);
}