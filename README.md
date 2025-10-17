
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

$loanAmount = 200000;
$termMonths = 60;
$annualInterestRate = 12;

$loanData = [
    'loan_amount' => $loanAmount,
    'interest' => $annualInterestRate,
    'term_months' => $termMonths,
    'starting_date' => new \DateTime('2024-01-01'),
    'remaining_months' => $termMonths, // Set to $termMonths for new loan, or less if partially paid
];

$loan = new LoanAmortization($loanData);

// Get all results (summary and schedule)
$results = $loan->getResults();

// Access summary
echo "Monthly Payment: $" . number_format($results['summary']['monthly_repayment'], 2) . "\n";
echo "Total Interest: $" . number_format($results['summary']['total_interest'], 2) . "\n";
echo "Total Payment: $" . number_format($results['summary']['total_pay'], 2) . "\n";

// Access payment schedule
foreach ($results['schedule'] as $payment) {
    [$status, $details] = $payment;
    echo sprintf(
        "%s - Date: %s, Payment: $%.2f, Principal: $%.2f, Interest: $%.2f, Balance: $%.2f\n",
        strtoupper($status),
        $details['date'],
        $details['payment'],
        $details['principal'],
        $details['interest'],
        $details['balance']
    );
}
```

### Example with Partial Payment

If a loan has already been partially paid:

```php
$loanData = [
    'loan_amount' => 200000,
    'interest' => 12,
    'term_months' => 60,
    'starting_date' => new \DateTime('2024-01-01'),
    'remaining_months' => 36, // 24 months already paid, 36 remaining
];

$loan = new LoanAmortization($loanData);
$results = $loan->getResults();

// The schedule will show 24 months as 'paid' and 36 as 'not_paid'
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
