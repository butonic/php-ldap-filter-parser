<?php

require __DIR__ . '/vendor/autoload.php';

use Butonic\Syntax\LDAP\FilterLexer;
use Butonic\Syntax\LDAP\FilterParser;


function testParser($filter) {
    $lexer = new FilterLexer($filter);
    $parser = new FilterParser($lexer);
    try {
        $parser->filter(); // begin parsing at rule list
    } catch (\Exception $e) {
        echo "Error at pos {$lexer->getPos()} in $filter\n";
        throw $e;
    }

    if ($parser->lookahead->type === FilterLexer::EOF_TYPE) {
        echo "$filter matches\n";
    } else {
        echo "$filter does not match completely, rest {$parser->lookahead}\n";
    }
}

if (isset($argv[1])) {
    testParser($argv[1]);
} else {
    $filters = [
        // from https://tools.ietf.org/html/rfc4515#section-4
        '(cn=Babs Jensen)',
        '(!(cn=Tim Howes))',
        '(&(objectClass=Person)(|(sn=Jensen)(cn=Babs J*)))',
        '(o=univ*of*mich*)',
        '(seeAlso=)',

        '(cn:caseExactMatch:=Fred Flintstone)',
        '(cn:=Betty Rubble)',
        '(sn:dn:2.4.6.8.10:=Barney Rubble)',
        '(o:dn:=Ace Industry)',
        '(:1.2.3:=Wilma Flintstone)',
        '(:DN:2.4.6.8.10:=Dino)',

        '(o=Parens R Us \\28for all your parenthetical needs\\29)',
        '(cn=*\\2A*)',
        '(filename=C:\\5cMyFile)',
        '(bin=\\00\\00\\00\\04)',
        '(sn=Lu\\c4\\8di\\c4\\87)',
        '(1.3.6.1.4.1.1466.0=\\04\\02\\48\\69)',

        // https://doc.owncloud.org/server/10.0/admin_manual/configuration/user/user_auth_ldap.html
        '(&(objectClass=inetOrgPerson)(memberOf=cn=owncloudusers,ou=groups,dc=example,dc=com))',
        '(&(objectClass=inetOrgPerson)(memberOf=cn=owncloudusers,ou=groups,dc=example,dc=com)(|(uid=%uid)(mail=%uid)))',

        // seen in the wild
        '(&(objectClass=*))',
        '(|(&(|(objectclass=person))(|(|(memberof=CN=Portal SecureB,OU=ITA Mail Groups,OU=France,OU=Central,OU=BMF Europe,DC=aiu,DC=sometech,DC=com)(primaryGroupID=840317))))(samaccountname=k2509)(samaccountname=k12305))',
        '(|(&(|(objectclass=person))(|(|(memberof=CN=Portal SecureB,OU=ITA Mail Groups,OU=France,OU=Central,OU=BMF Europe,DC=aiu,DC=sometech,DC=com)(primaryGroupID=840317))(&(objectclass=person)(|(samaccountname=k2509)(samaccountname=k12305)))))(samaccountname=%uid))',
        '(&(objectclass=*)(uniService=CLOUD))',
        '(&(uniService=CLOUD)(uid=%uid))',
        '(&(objectCategory=person)(objectClass=user)(memberOf:1.2.840.113556.1.4.1941:=CN=MyInternet4all-Global_Users,OU=Groups,OU=OEM,OU=MY,DC=emea,DC=my-world,DC=com))',

    ];
    foreach ($filters as $filter) {
        testParser($filter);
    }
}