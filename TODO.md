# TODO: Improvements

> [!IMPORTANT]
> Probably next version
> The `return` stuff might have some breaking changes

## 1. Input Validation for Business Logic

### Issue
The package currently accepts invalid input values that can lead to nonsensical calculations:
- Negative values for `loan_amount`, `interest`, and `term_months` are accepted
- `remaining_months` can be negative or greater than `term_months`
- Zero `term_months` silently defaults to 1 instead of throwing a clear error

### Current Problem Example
```php
$loan = new LoanAmortization([
    'loan_amount' => -50000,  // Negative! Should fail
    'interest' => -10,        // Negative! Should fail
    'term_months' => 12,
    'starting_date' => new DateTime(),
    'remaining_months' => 999 // Greater than term_months! Should fail
]);
// This will run but produce nonsensical results
```

### Suggested Solution
- Add validation in the `validate()` method to check:
  - `loan_amount` > 0
  - `interest` >= 0 (allow zero for interest-free loans)
  - `term_months` > 0
  - `remaining_months` >= 0 and <= `term_months`
- Throw descriptive `InvalidArgumentException` messages for each validation failure
- Consider adding a `LoanValidationException` class for more specific error handling

### Impact
- Prevents users from accidentally creating invalid loans
- Provides clear error messages for debugging
- Makes the API more robust and predictable

---

## 2. API Design: Schedule Structure

### Issue
The current schedule structure uses an awkward array format that requires destructuring:
```php
// Current format: [$status, $details]
[$status, $details] = $schedule[0];
echo $details['payment'];
```

### Current Problem
- Requires array destructuring to access data
- Status and payment details are mixed in a confusing way
- No clear data structure documentation
- Hard to work with in modern PHP applications

### Suggested Solution
Refactor to use associative arrays:
```php
// Better format:
$schedule = [
    [
        'status' => 'paid',
        'payment' => 8884.88,
        'interest' => 1000.00,
        'principal' => 7884.88,
        'balance' => 92115.12,
        'date' => '2023-02-01'
    ],
    // ...
];
```

### Impact
- More intuitive API for developers
- Easier to work with in templates and APIs
- Better IDE support and autocompletion
- Cleaner code when processing schedules

---

## 3. Advanced Features for Real-World Use

### Issue
The package is limited to basic monthly payments with simple interest calculations, missing features commonly needed in real-world loan scenarios.

### Current Limitations
- Only supports monthly payments
- No support for different compounding periods (daily, quarterly, etc.)
- No support for extra/additional payments
- No reverse calculation (calculate loan amount from desired payment)
- No support for grace periods or payment delays
- No support for different payment frequencies (biweekly, weekly)

### Suggested Solution
Add new features while maintaining backward compatibility:

#### 3.1 Payment Frequency Support
```php
$loanData = [
    'loan_amount' => 200000,
    'interest' => 12,
    'term_months' => 60,
    'payment_frequency' => 'biweekly', // 'monthly', 'weekly', 'biweekly'
    'starting_date' => new DateTime('2024-01-01'),
    'remaining_months' => 60,
];
```

#### 3.2 Extra Payments
```php
$loanData = [
    // ... basic loan data
    'extra_payments' => [
        ['month' => 12, 'amount' => 5000],
        ['month' => 24, 'amount' => 3000],
    ]
];
```

#### 3.3 Reverse Calculation
```php
$loan = LoanAmortization::calculateFromPayment([
    'desired_payment' => 2000,
    'interest' => 12,
    'term_months' => 60,
    'starting_date' => new DateTime('2024-01-01'),
]);
```

#### 3.4 Compounding Periods
```php
$loanData = [
    // ... basic loan data
    'compounding_period' => 'daily', // 'monthly', 'quarterly', 'daily'
];
```

### Impact
- Makes the package suitable for real-world financial applications
- Supports various loan types (mortgages, personal loans, business loans)
- Provides flexibility for different payment scenarios
- Increases adoption and usefulness of the package

---

## Implementation Priority

1. **High Priority**: Input Validation (#1) - Critical for preventing bugs
2. **Medium Priority**: API Design (#2) - Improves developer experience
3. **Low Priority**: Advanced Features (#3) - Nice-to-have enhancements

## Notes

- All changes should maintain backward compatibility
- Add comprehensive tests for new features
- Update documentation and examples
- Consider version bumping for breaking changes (if any)
