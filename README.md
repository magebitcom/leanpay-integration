# Leanpay payment plugin for Magento 2

The Magento 2 module `Leanpay_Payment` lets a shop take Leanpay installment payments and show the monthly installment next to product prices, for Leanpay's Slovenian, Croatian and Romanian markets. It also lets a merchant attach Leanpay's special financing programs to products, categories or whole carts.

Merchants install it from [Packagist](https://packagist.org/packages/leanpay/payment) as `leanpay/payment`:

```
composer require leanpay/payment
bin/magento setup:upgrade
```

Then enable Leanpay under Stores > Configuration > Sales > Payment Methods and enter the API key and secret word of the Leanpay vendor account. A Hyva storefront also needs the add-on `leanpay/payment-hyva`, which requires this module. Updates: `composer require leanpay/payment:^<version>`, then the Magento upgrade, compile and cache commands. Release notes are in [docs/releases](docs/releases/README.md).

## Repositories

- [leanpay-hyva-compatibility](https://github.com/magebitcom/leanpay-hyva-compatibility): the Hyva widget and Hyva Checkout component, depends on this module.
- [leanpay-magento-website](https://github.com/magebitcom/leanpay-magento-website): the Magebit test shops and local stack, which include this module as a submodule.

Jira tickets use the prefix `LMM`. The branch and release rules are in [AGENTS.md](AGENTS.md).

## Docs

- [docs/architecture](docs/architecture/README.md): how the module plugs into Magento, what it stores, how the Hyva add-on depends on it.
- [docs/functionality](docs/functionality/README.md): paying with Leanpay, installment display, promotions, installment plans.
- [docs/integrations](docs/integrations/README.md): the Leanpay API.
- [docs/operations](docs/operations/local-setup.md): developing the plugin, the runbook with the release steps, troubleshooting.
- [docs/testing](docs/testing/README.md): what is tested and how.
