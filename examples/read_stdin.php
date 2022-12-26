<?php
while(true) {
    $line = fgets(STDIN);
    if ($line) {
        echo "stdin return: ", $line;
    } else {
        break;
    }
}
