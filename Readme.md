# Ldap filter parser

Can be used to parse [RFC4515](https://tools.ietf.org/html/rfc4515) style LDAP filter strings.

Goal is to provide end user understandable error messages.
 
## Usage 

```console
test_parser.php '(cn=foo)'
```

## Tests

For now just run `test_parser.php` without any arguments.
It will test a few examples from the rfc as well as some
filters seen in the wild:
```
// from https://tools.ietf.org/html/rfc4515#section-4
(cn=Babs Jensen)
(!(cn=Tim Howes))
(&(objectClass=Person)(|(sn=Jensen)(cn=Babs J*)))
(o=univ*of*mich*)
(seeAlso=)

(cn:caseExactMatch:=Fred Flintstone)
(cn:=Betty Rubble)
(sn:dn:2.4.6.8.10:=Barney Rubble)
(o:dn:=Ace Industry)
(:1.2.3:=Wilma Flintstone)
(:DN:2.4.6.8.10:=Dino)

(o=Parens R Us \28for all your parenthetical needs\29)
(cn=*\2A*)
(filename=C:\5cMyFile)
(bin=\00\00\00\04)
(sn=Lu\c4\8di\c4\87)
(1.3.6.1.4.1.1466.0=\04\02\48\69)

// https://doc.owncloud.org/server/10.0/admin_manual/configuration/user/user_auth_ldap.html
(&(objectClass=inetOrgPerson)(memberOf=cn=owncloudusers,ou=groups,dc=example,dc=com))
(&(objectClass=inetOrgPerson)(memberOf=cn=owncloudusers,ou=groups,dc=example,dc=com)(|(uid=%uid)(mail=%uid)))

// seen in the wild
(&(objectClass=*))
(|(&(|(objectclass=person))(|(|(memberof=CN=Portal SecureB,OU=ITA Mail Groups,OU=France,OU=Central,OU=BMF Europe,DC=aiu,DC=sometech,DC=com)(primaryGroupID=840317))))(samaccountname=k2509)(samaccountname=k12305))
(|(&(|(objectclass=person))(|(|(memberof=CN=Portal SecureB,OU=ITA Mail Groups,OU=France,OU=Central,OU=BMF Europe,DC=aiu,DC=sometech,DC=com)(primaryGroupID=840317))(&(objectclass=person)(|(samaccountname=k2509)(samaccountname=k12305)))))(samaccountname=%uid))
(&(objectclass=*)(uniService=CLOUD))
(&(uniService=CLOUD)(uid=%uid))
(&(objectCategory=person)(objectClass=user)(memberOf:1.2.840.113556.1.4.1941:=CN=MyInternet4all-Global_Users,OU=Groups,OU=OEM,OU=MY,DC=emea,DC=my-world,DC=com))
```

## TODO
- [ ] cleanup ... get rid of some experiments, eg. ByteLexer
- [ ] show snippet of error location
- [ ] unit Tests
- [ ] build ast
- [ ] use ast to provide better error reporting?
- [ ] make usable as a lib?
- [ ] translations? or keep that separate?
- [ ] composer
- [ ] performance vs eg https://github.com/hoaproject/Compiler