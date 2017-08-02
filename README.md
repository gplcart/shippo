[![Build Status](https://scrutinizer-ci.com/g/gplcart/shippo/badges/build.png?b=master)](https://scrutinizer-ci.com/g/gplcart/shippo/build-status/master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/gplcart/shippo/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/gplcart/shippo/?branch=master)

Shippo is a [GpL Cart](https://github.com/gplcart/gplcart) module that integrates [Shippo](https://goshippo.com) multi-carrier shipping API into your shopping cart. It's 30+ worldwide carriers in your pocket!
Shippo's API is mostly free, you pay only for retrieving shipping labels and tracking numbers.

**Features:**

- Calculation of shipping rates on checkout page
- Retrieving shipping labels and tracking numbers (paid feature)

**Requirements:**

- CURL

**Installation:**

1. Download and extract to `system/modules` manually or using composer `composer require gplcart/shippo`. IMPORTANT: If you downloaded the module manually, be sure that the name of extracted module folder doesn't contain a branch/version suffix, e.g `-master`. Rename if needed.
2. Go to `admin/module/list` end enable the module
3. Register an account on https://goshippo.com
3. Enter your test/live API tokens and adjust other settings at `admin/module/settings/shippo`

Print shipping labels at `admin/tool/shippo`

