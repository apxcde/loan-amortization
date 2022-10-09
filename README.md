
# PHP composer package for loan amortization

[![Latest Version on Packagist](https://img.shields.io/packagist/v/apxcde/loan-amortization.svg?style=flat-square)](https://packagist.org/packages/apxcde/loan-amortization)
[![Tests](https://github.com/apxcde/loan-amortization/actions/workflows/run-tests.yml/badge.svg?branch=main)](https://github.com/apxcde/loan-amortization/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/apxcde/loan-amortization.svg?style=flat-square)](https://packagist.org/packages/apxcde/loan-amortization)

## Installation

You can install the package via composer:

```bash
composer require apxcde/loan-amortization
```

## Usage

```php
use Apxcde\LoanAmortization\LoanAmortization;

$amount = 200000;
$termYears = 5;
$annualInterestRate = 12;

$loan_data = [
    'loan_amount' => (float) $amount,
    'term_years' => $termYears,
    'interest' => $annualInterestRate,
    'term_months' => $termYears * 12,
    'starting_date' => Carbon::now(),
    'remaining_months' => $termYears * 12,
];

$loan_calculation = new LoanAmortization($loan_data);
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [ApexCode](https://github.com/apxcde)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
