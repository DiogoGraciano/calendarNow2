<?php

$recrete = $argv[1]??null;

require 'bootstrap.php';

use app\db\migrations\migrate;

migrate::execute(!empty($recrete));