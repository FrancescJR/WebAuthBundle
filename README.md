Noob Bundle. But making use of github and packagist to reuse my own bundles. one day I might write something more usefull for the community.

Forgive my lack of knowledge of .md format.

Installation:

1. Follow readme for imag/ldap bundle, with the following things:

1. Download with composer -> do the same also with this bundle
3. Enable the Bundle -> do the same for this bundle
4. Configure LdapBundle security.yml -> in WebAuthBundle/Resources/Docs/security.yml you will find an example
7. Import LdapBundle routing -> DON'T DO THAT
--from here down you don't have to follow it
8. Implement Logout ->it depends on use case, but if you have users logged in with webauth, you wont let them logout!
9. Use chain provider -
10. Subscribe to PRE_BIND event


2. Import WebAuthBundle routing:
oist_webauth:
  resource: "@OISTWebAuthBundle/Resources/config/routing.yml"

3. Set the name of your firewall that will require webauth:
#app/config/config.yml
oist_web_auth:
    firewall_name: secured_area

by default it is: secured_area, change accordingly your security.yml file.


4. Set Symfony to not override session values from main $_SESSION variable in php. As we need that to get the already identified user.
#app/config/config.yml
framework:
    session:
        save_path: ~
        
If you have set the security.yml like the example, you will have by default the user and its groups as roles that has been logged in through WebAuth previously.
That means that it expects that you have set up apache with the webAuth settings.