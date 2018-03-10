[![Build Status](https://scrutinizer-ci.com/g/gplcart/shippo/badges/build.png?b=master)](https://scrutinizer-ci.com/g/gplcart/shippo/build-status/master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/gplcart/shippo/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/gplcart/shippo/?branch=master)

Shippo is a [GpL Cart](https://github.com/gplcart/gplcart) module that integrates [Shippo](https://goshippo.com) multi-carrier shipping API into your shopping cart. It's 30+ worldwide carriers in your pocket!
Shippo's API is mostly free, you pay only for retrieving shipping labels and tracking numbers.

**Features:**

- Calculation of shipping rates on checkout page
- Retrieving shipping labels and tracking numbers (paid feature)

**Requirements:**

- CURL

**Installation:**

This module requires 3-d party library which should be downloaded separately. You have to use [Composer](https://getcomposer.org) to download all the dependencies.

1. From your web root directory: `composer require gplcart/shippo`. If the module was downloaded and placed into `system/modules` manually, run `composer update` to make sure that all 3-d party files are presented in the `vendor` directory.
2. Go to `admin/module/list` end enable the module
3. Register an account on https://goshippo.com
3. Enter your test/live API tokens and adjust other settings at `admin/module/settings/shippo`

Print shipping labels at `admin/tool/shippo`

