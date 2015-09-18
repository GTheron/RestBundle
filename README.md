# GTheronRestBundle

This bundle depends on [FOSRestBundle](https://github.com/FriendsOfSymfony/FOSRestBundle). In a word, it aims at providing a base for REST resource definition and CRUD manipulation.

## What is this bundle?

If you're working on a REST api with Symfony2, you probably have used [FOSRestBundle](https://github.com/FriendsOfSymfony/FOSRestBundle). It offers functionnality for controllers and automatic routing, which is remarkably handy for building any REST api.

However, after having used it for a relatively large api, with lots of entities - which in the REST vocabulary would be Resources - I still had a lot of boilerplate code, especially since almost all resources needed at least basic CRUD functions.
This meant code repetition in Controllers, Services and Models.

This bundle aims to provide a base definition for exposed Resources, as well as a service that provides CRUD operations for all resources, and abstract controllers with boilerplate code for the most common functions (cget, get, post and put - delete being quite trivial to write generally).

Note that as the moment of writing, I basically just ported code. There are a few things I need to work on before it's quite ready for use:

| Feature | State |
| ------------- | ------------- |
| Making authorization checks optional | Written, being tested |
| Offering more/better events in the validation cycle | Written, being tested |
| Automatic event generation  | Written, being tested |
| Recursive resources (sub resource atm) | In design phase |

Feel free to take a look at the code and provide suggestions!

## Requirements

[FOSRestBundle](https://github.com/FriendsOfSymfony/FOSRestBundle) will be required with composer on install.
However, at this time, the Symfony ACL system is required to be initialized and ready for use (this is temporary, as it will only be needed in the future if you use the security options of the bundle).
Refer to this doc to install it: http://symfony.com/doc/current/cookbook/security/acl.html
