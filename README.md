# JSHint plugin for [PHPCI](https://www.phptesting.org)

A plugin for PHPCI to check your Javascript using JSHint.

### Install the Plugin

1. Navigate to your PHPCI root directory and run `composer require thijskok/phpci-jshint-plugin`
2. Copy `build-plugins/jshint.js` to `/path/to/phpci/public/assets/js/build-plugins/jshint.js`
2. If you are using the PHPCI daemon, restart it
3. Update your `phpci.yml` in the project you want to deploy with

### Prerequisites

1. [JSHint](http://jshint.com/install/) needs to be installed.

### Plugin Options
- **path** _[string, optional]_ - Directory in which PHPMD should run (default: build root)
- **allowed_warnings** _[int, optional]_ - The warning limit for a successful build (default: 0). -1 disables warnings

### PHPCI Config

```yml
ThijsKok\PHPCI\Plugin\JSHint:
    path: 'resources/js'
    allowed_warnings: 10
```

example:

```yml
setup:
    ThijsKok\PHPCI\Plugin\JSHint:
        path: 'resources/js'
```
