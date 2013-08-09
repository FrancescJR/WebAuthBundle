Noob Bundle. But making use of github and packagist to reuse my own bundles. one day I might write something more usefull for the community.

Forgive my lack of knowledge of .md format.

Installation:

### 1. Follow readme for imag/ldap bundle, with the following things:

 Download with composer -> do the same also with this bundle
 Enable the Bundle -> do the same for this bundle
 Configure LdapBundle security.yml -> in WebAuthBundle/Resources/Docs/security.yml you will find an example
 Import LdapBundle routing -> DON'T DO THAT
 --from here down you don't have to follow it
 Implement Logout ->it depends on use case, but if you have users logged in with webauth, you wont let them logout!
 Use chain provider -
 Subscribe to PRE_BIND event


### 2. Import WebAuthBundle routing:
config/routing.yml:
```
oist_webauth:
  resource: "@OISTWebAuthBundle/Resources/config/routing.yml"
```
### 3.I am lazy to make it through the config, so just add a route called _homeafterlogin in your routing!!

For example:
to any routing file that it is read by the Kernel:
```
 _homeafterlogin:
     pattern: /hello/OISTWebAuthInstalled
     defaults: { _controller: "MyCompanyRecentlyCreatedBundle:Default:index" }
```
### 4. Set the name of your firewall that will require webauth:
```#app/config/config.yml
oist_web_auth:
    firewall_name: secured_area
```
by default it is: secured_area, change accordingly your security.yml file.


### 5. (NOT IN Symfony 2.3, only 2.1) Set Symfony to not override session values from main $_SESSION variable in php. As we need that to get the already identified user.
```#app/config/config.yml
framework:
    session:
        save_path: ~
```       
If you have set the security.yml like the example, you will have by default the user and its groups as roles that has been logged in through WebAuth previously.
That means that it expects that you have set up apache with the webAuth settings.

### Contact
To contact me for this mess: francesc.travesa@oist.jp

For more infor I just copy paste the README.md from imag/ldap. So you know what I'm speaking about:
### -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
# LdapBundle

LdapBundle provides LDAP authentication without using Apache's `mod_ldap`. The bundle instead relies on PHP's [LDAP extension](http://php.net/manual/en/book.ldap.php) along with a form to authenticate users. LdapBundle also can also be used for authorization by retrieving the user's roles defined in LDAP.

## Contact

Nick: aways
IRC: irc.freenode.net - #symfony-fr

## Install

1. Download with composer
2. Enable the Bundle
3. Configure LdapBundle in security.yml
4. Import LdapBundle routing
5. Implement Logout
6. Use chain provider
7. Subscribe to PRE_BIND event

### Get the Bundle

### Composer
Add LdapBundle in your project's `composer.json`

```json
{
    "require": {
        "imag/ldap-bundle": "dev-master"
    }
}
```

### Enable the Bundle

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new IMAG\LdapBundle\IMAGLdapBundle(),
    );
}
```

### Configure security.yml

**Note:**
> An example `security.yml` file is located within the bundle at `./Resources/Docs/security.yml`

``` yaml
# ./IMAG/LdapBundle/Resources/config/security.yml

security:
  firewalls:
    restricted_area:
      pattern:          ^/
      anonymous:        ~
      provider:         ldap
      imag_ldap:        ~
      # alternative configuration
      # imag_ldap:
      #   login_path:   /ninja/login
      logout:
        path:           /logout
        target:         /

  providers:
    ldap:
      id: imag_ldap.security.user.provider
                
  encoders:
    IMAG\LdapBundle\User\LdapUser: plaintext

  access_control:
    - { path: ^/login,          roles: IS_AUTHENTICATED_ANONYMOUSLY }
    - { path: ^/,               roles: IS_AUTHENTICATED_FULLY }

imag_ldap:
  client:
    host: your.host.foo
    port: 389
#    version: 3 # Optional
#    username: foo # Optional
#    password: bar # Optional
#    network_timeout: 10 # Optional
#    referrals_enabled: true # Optional
#    bind_username_before: true # Optional

  user:
    base_dn: ou=people,dc=host,dc=foo
#    filter: (&(foo=bar)(ObjectClass=Person)) #Optional
    name_attribute: uid
  role:
    base_dn: ou=group, dc=host, dc=foo
#    filter: (ou=group) #Optional
    name_attribute: cn
    user_attribute: member
    user_id: [ dn or username ]
```

**You should configure the parameters under the `imag_ldap` section to match your environment.**

**Note:**

> The optional parameters have default values if not set.
> You can disable default values by setting a parameter to NULL.

``` yaml
# app/config/security.yml
imag_ldap:
  # ...
  role:
    # ...
    filter: NULL
```

### Import routing

``` yaml
# app/config/routing.yml

imag_ldap:
  resource: "@IMAGLdapBundle/Resources/config/routing.yml"
```

### Implement Logout

Just create a link with a logout target.

``` html
<a href="{{ path('logout') }}">Logout</a>
```

**Note:**
> You can refer to the official Symfony documentation :
> http://symfony.com/doc/current/book/security.html#logging-out

### Chain provider ###

You can also chain the login form with other providers, such as database_provider, in_memory provider, etc.

``` yml
# app/config/security.yml
security:
    firewalls:
        secured_area:
            pattern: ^/
            anonymous: ~
            imag_ldap:
                provider: multiples
            logout:
                path: logout
    providers:
        multiples:
            chain:
                providers: [ldap, db]          
        ldap:
            id: imag_ldap.security.user.provider
        db:
            entity: { class: FQDN\User }
```

**Note:**
> If you have set the config option `bind_username_before: true` you must chain the providers with the ldap provider in the last position.

``` yml
# app/config/security.yml

providers: [db, ldap]          
```

### Subscribe to PRE_BIND event

The PRE_BIND is fired before the user is authenticated via LDAP. Here you can write a listener to perform your own logic before the user is bound/authenticated to LDAP.
For example, to add your own roles or do other authentication/authorization checks with your application.

If you want to break the authentication process within your listener, throw an Exception.

Example listener:
``` xml
<service id="ldap.listener" class="Acme\HelloBundle\EventListener\LdapSecuritySubscriber">
    <tag name="kernel.event_subscriber" />
</service>
```

Example:
```php
<?php

namespace Acme\HelloBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use IMAG\LdapBundle\Event\LdapUserEvent;

/**
 * Performs logic before the user is found to LDAP
 */
class LdapSecurityListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            \IMAG\LdapBundle\Event\LdapEvents::PRE_BIND => 'onPreBind',
        );
    }

    /**
     * Modifies the User before binding data from LDAP
     *
     * @param \IMAG\LdapBundle\Event\LdapUserEvent $event
     */
    public function onPreBind(LdapUserEvent $event)
    {
        $user = $event->getUser();
        $config = $this->appContext->getConfig();

        $ldapConf = $config['ldap'];

        if (!in_array($user->getUsername(), $ldapConf['allowed'])) {
            throw new \Exception(sprintf('LDAP user %s not allowed', $user->getUsername()));
        }

        $user->addRole('ROLE_LDAP');
    }
}
```
