<?php

require __DIR__ . '/vendor/autoload.php';

use Butonic\Syntax\LDAP\FilterLexer;
use Butonic\Syntax\Lexer;

$lexer = new FilterLexer($argv[1]);
$token = $lexer->nextToken();

while($token->type !== Lexer::EOF_TYPE) {
    echo $token . "\n";
    $token = $lexer->nextToken();
}


/*
$data = 'ä';
print_r($data);

$unpacked = unpack('C*', $data);
print_r($unpacked);

$packed = pack('C*', $unpacked[1],$unpacked[2]);
print_r($packed);
*/