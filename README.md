STACK-2 Authentication Middlewares
==================================

A collection of [Stack][0] middlewares designed to help authentication
middleware implementors adhere to the [STACK-2 Authentication][1] conventions.


Installation
------------

Through [Composer][2] as [dflydev/stack-authentication][3].


Middlewares
-----------

### Authentication Middleware

The Authentication middleware takes care of setting up the handling of an
inbound request by taking care of some [STACK-2 Authentication][2] housekeeping
tasks:

 * If the `stack.authn.token` is set, it wraps the application in
   `WwwAuthenticateStackChallenge` and delegates.
 * If the there is an `authorization` header, it returns the result of then
   **authenticate** callback.
 * If anonymous requests are received and anonymous requests are allowed, it
   wraps the application in `WwwAuthenticateStackChallenge` and delegates.
 * Otherwise, it returns the result of the **challenge** callback.

#### Usage

```php
<?php

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

$challenge = function (Response $response) {
    // Assumptions that can be made:
    // * 401 status code
    // * WWW-Authenticate header with a value of "Stack"
    //
    // Expectations:
    // * MAY set WWW-Authenticate header to another value
    // * MAY return a brand new response (does not have to be
    //   the original response)
    // * MUST return a response
    return $response;
};

$authenticate = function (HttpKernelInterface $app, $anonymous) {
    // Assumptions that can be made:
    // * The $app can be delegated to at any time
    // * The anonymous boolean indicates whether or not we
    //   SHOULD allow anonymous requests through or if we
    //   should challenge immediately.
    // * Additional state, like $request, $type, and $catch
    //   should be passed via use statement if they are needed.
    //
    // Expectations:
    // * SHOULD set 'stack.authn.token' attribute on the request
    //   when authentication is successful.
    // * MAY delegate to the passed $app
    // * MAY return a custom response of any status (for example
    //   returning a 302 or 400 status response is allowed)
    // * MUST return a response
};

return (new Authentication($app, [
        'challenge' => $challenge,
        'authenticate' => $authenticate,
        'anonymous' => true, // default: false
    ]))
    ->handle($request, $type, $catch);
```

### WwwAuthenticateStackChallenge Middleware

The WwwAuthenticateStackChallenge middleware takes care of setting up the
handling of an outbound response by taking care of some
[STACK-2 Authentication][2] housekeeping tasks:

 * If the response has a 401 status code and has a WWW-Authenticate header with
   the value of Stack, it returns the result of the **challenge** callback.
 * Otherwise the original response from the delegated app is returned.


#### Usage

```php
<?php

use Symfony\Component\HttpFoundation\Response;

$challenge = function (Response $response) {
    // Assumptions that can be made:
    // * 401 status code
    // * WWW-Authenticate header with a value of "Stack"
    //
    // Expectations:
    // * MAY set WWW-Authenticate header to another value
    // * MAY return a brand new response (does not have to be
    //   the original response)
    // * MUST return a response
    return $response;
};

return (new WwwAuthenticateStackChallenge($app, $challenge))
    ->handle($request, $type, $catch);
```


License
-------

MIT, see LICENSE.


Community
---------

If you have questions or want to help out, join us in the **#stackphp** or
**#dflydev** channels on **irc.freenode.net**.


[0]: http://stackphp.com/
[1]: http://stackphp.com/specs/STACK-2/
[2]: http://getcomposer.org
[3]: https://packagist.org/packages/dflydev/stack-authentication
