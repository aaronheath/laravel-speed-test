# Bandwidth Check

[![Build Status](https://travis-ci.org/aaronheath/laravel-speed-test.svg?branch=master)](https://travis-ci.org/aaronheath/laravel-speed-test)

## Introduction

This is a personal package to facilitate scheduled downstream bandwidth checks.
 
## Installation

This package is installed via [Composer](https://getcomposer.org/). 

Before installing, the repository, along with other private packages, must be added to the repositories section of the host projects composer.json.

```text
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/aaronheath/laravel-speed-test"
    },
    {
        "type": "vcs",
        "url": "https://github.com/aaronheath/oauth-client"
    },
    {
        "type": "vcs",
        "url": "https://github.com/aaronheath/class-logger"
    }
],
```

To install, run the following command.

```bash
composer require aaronheath/laravel-speed-test
```

Then, publish the configuration file. New file will be created at config/bandwidth-check.php. 

Another new configuration file maybe created for oauth-client. Please refer to [that project](https://github.com/aaronheath/oauth-client) for further details.

```bash
php artisan vendor:publish
```

Finally, you'll want to configure the bandwidth checker by updating the projects .env file. Update values as required.

```text
BANDWIDTH_CHECK_ENABLED=true
BANDWIDTH_CHECK_REPORT_URL=https://example.com/api/bandwidth-check
BANDWIDTH_CHECK_RUNNER=docker # or system
```
## Performing Bandwidth Checks

### Via CLI

Once properly configured, bandwidth checks can be performed via cli.

```bash
php artisan bandwidth-check:run
```

By executing this command, a check will be performed and results sent to the remote repository.

### Via Job

Bandwidth checks can also be dispatched onto the queue via a job.

```php
dispatch(new Heath\BandwidthCheck\BandwidthCheckJob);
```

### Directly

A bandwidth check can also be actioned synchronously.

```php
(new Heath\BandwidthCheck\BandwidthCheck)->run();
```

BandwidthCheck also includes a handy isEnabled() method to help ensure that checks are enabled.

```php
(new Heath\BandwidthCheck\BandwidthCheck)->isEnabled(); // bool
```