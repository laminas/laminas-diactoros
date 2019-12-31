# laminas-diactoros

Master:
[![Build status][Master image]][Master]
[![Coverage Status][Master coverage image]][Master coverage]
Develop:
[![Build status][Develop image]][Develop]
[![Coverage Status][Develop coverage image]][Develop coverage]

> Diactoros (pronunciation: `/dɪʌktɒrɒs/`): an epithet for Hermes, meaning literally, "the messenger."

This package supercedes and replaces [phly/http](https://github.com/phly/http).

`laminas-diactoros` is a PHP package containing implementations of the [accepted PSR-7 HTTP message interfaces](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-7-http-message.md), as well as a "server" implementation similar to [node's http.Server](http://nodejs.org/api/http.html).

* File issues at https://github.com/laminas/laminas-diactoros/issues
* Issue patches to https://github.com/laminas/laminas-diactoros/pulls

## Documentation

Documentation is [in the doc tree](doc/), and can be compiled using [bookdown](http://bookdown.io):

```console
$ bookdown doc/bookdown.json
$ php -S 0.0.0.0:8080 -t doc/html/ # then browse to http://localhost:8080/
```

> ### Bookdown
>
> You can install bookdown globally using `composer global require bookdown/bookdown`. If you do
> this, make sure that `$HOME/.composer/vendor/bin` is on your `$PATH`.

  [Master]: https://travis-ci.org/laminas/laminas-diactoros
  [Master image]: https://travis-ci.org/laminas/laminas-diactoros.svg?branch=master
  [Master coverage image]: https://img.shields.io/coveralls/laminas/laminas-diactoros/master.svg
  [Master coverage]: https://coveralls.io/r/laminas/laminas-diactoros?branch=master
  [Develop]: https://github.com/laminas/laminas-diactoros/tree/develop
  [Develop image]:  https://travis-ci.org/laminas/laminas-diactoros.svg?branch=develop
  [Develop coverage image]: https://coveralls.io/repos/laminas/laminas-diactoros/badge.svg?branch=develop
  [Develop coverage]: https://coveralls.io/r/laminas/laminas-diactoros?branch=develop
