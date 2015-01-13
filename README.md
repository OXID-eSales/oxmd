OXMD
====

OXMD is an extension of [PHPMD](http://phpmd.org/) that applies metrics according to [OXID module certification guidelines](http://wiki.oxidforge.org/Certification/Modules) and computes the expected module certification price.

OXMD was developed in cooperation between [OXID eSales AG](http://www.oxid-esales.com) and [Qafoo GmbH](http://qafoo.com).

Installation
------------

* Install PHP with XDebug
* [Install composer](https://getcomposer.org/doc/00-intro.md#installation-nix)
* Run `php composer.phar create-project phpmd/oxmd`

Run
---

* Run the unit tests of your module and make sure you generate a clover coverage report with the `--coverage-clover` option
* In the OXMD directory run `src/bin/oxmd`, it will show you the available options.

License
-------

OXMD is licensed under the BSD-3-Clause.
