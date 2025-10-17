<?php

declare(strict_types=1);

use Apxcde\LoanAmortization\LoanAmortization;

it('calculates monthly payment correctly', function () {
    $loanData = [
        'loan_amount' => 100000.0,
        'term_years' => 1,
        'interest' => 12,
        'term_months' => 12,
        'starting_date' => new DateTime('2023-01-01'),
        'remaining_months' => 12,
    ];

    $loan = new LoanAmortization($loanData);
    $summary = $loan->getResults()['summary'];

    expect(round($summary['monthly_repayment'], 2))->toBe(8884.88);
});

it('provides correct summary totals', function () {
    $loanData = [
        'loan_amount' => 100000.0,
        'term_years' => 1,
        'interest' => 12,
        'term_months' => 12,
        'starting_date' => new DateTime('2023-01-01'),
        'remaining_months' => 12,
    ];

    $loan = new LoanAmortization($loanData);
    $summary = $loan->getResults()['summary'];

    $expectedTotalPay = $summary['monthly_repayment'] * 12;
    $expectedTotalInterest = $expectedTotalPay - 100000.0;

    expect($summary['total_pay'])->toEqualWithDelta($expectedTotalPay, 0.01);
    expect($summary['total_interest'])->toEqualWithDelta($expectedTotalInterest, 0.01);
});

it('handles zero interest', function () {
    $loanData = [
        'loan_amount' => 12000.0,
        'term_years' => 1,
        'interest' => 0,
        'term_months' => 12,
        'starting_date' => new DateTime('2023-01-01'),
        'remaining_months' => 12,
    ];

    $loan = new LoanAmortization($loanData);
    $summary = $loan->getResults()['summary'];

    expect(round($summary['monthly_repayment'], 2))->toBe(1000.00);
});

it('generates schedule with paid months when partial payments were made', function () {
    $loanData = [
        'loan_amount' => 10000.0,
        'term_years' => 1,
        'interest' => 12,
        'term_months' => 12,
        'starting_date' => new DateTime('2023-01-01'),
        'remaining_months' => 6,
    ];


    $loan = new LoanAmortization($loanData);
    $schedule = $loan->getResults()['schedule'];

    expect(count($schedule))->toBe(12);

    $paid = array_filter($schedule, fn ($row) => $row[0] === 'paid');
    $notPaid = array_filter($schedule, fn ($row) => $row[0] === 'not_paid');

    expect(count($paid))->toBe(6)
        ->and(count($notPaid))->toBe(6);
});

it('throws an exception when required keys are missing', function () {
    $loanData = [
        'loan_amount' => 10000.0,
    ];

    expect(fn () => new LoanAmortization($loanData))
        ->toThrow(InvalidArgumentException::class, 'Missing required keys');
});

it('throws an exception when starting_date is not a DateTimeInterface', function () {
    $loanData = [
        'loan_amount' => 10000.0,
        'interest' => 10,
        'term_months' => 12,
        'starting_date' => '2023-01-01',
        'remaining_months' => 12,
    ];

    expect(fn () => new LoanAmortization($loanData))
        ->toThrow(InvalidArgumentException::class, 'starting_date must implement DateTimeInterface');
});

it('does not mutate the original DateTime object', function () {
    $originalDate = new DateTime('2023-01-01');
    $originalDateString = $originalDate->format('Y-m-d');

    $loanData = [
        'loan_amount' => 10000.0,
        'interest' => 12,
        'term_months' => 12,
        'starting_date' => $originalDate,
        'remaining_months' => 12,
    ];

    $loan = new LoanAmortization($loanData);
    $loan->getResults();

    // Original date should remain unchanged
    expect($originalDate->format('Y-m-d'))->toBe($originalDateString);
});

it('does not mutate the original DateTimeImmutable object', function () {
    $originalDate = new DateTimeImmutable('2023-01-01');
    $originalDateString = $originalDate->format('Y-m-d');

    $loanData = [
        'loan_amount' => 10000.0,
        'interest' => 12,
        'term_months' => 12,
        'starting_date' => $originalDate,
        'remaining_months' => 12,
    ];

    $loan = new LoanAmortization($loanData);
    $loan->getResults();

    // Original date should remain unchanged
    expect($originalDate->format('Y-m-d'))->toBe($originalDateString);
});

it('returns consistent results when getResults is called multiple times', function () {
    $loanData = [
        'loan_amount' => 50000.0,
        'interest' => 10,
        'term_months' => 24,
        'starting_date' => new DateTime('2023-01-01'),
        'remaining_months' => 24,
    ];

    $loan = new LoanAmortization($loanData);

    $results1 = $loan->getResults();
    $results2 = $loan->getResults();
    $results3 = $loan->getResults();

    // All results should be identical
    expect($results1)->toBe($results2)
        ->and($results2)->toBe($results3);

    // Verify schedule consistency
    expect($results1['schedule'][0][1]['date'])->toBe($results2['schedule'][0][1]['date'])
        ->and($results1['schedule'][0][1]['balance'])->toBe($results2['schedule'][0][1]['balance']);
});

it('maintains state integrity after multiple getResults calls', function () {
    $loanData = [
        'loan_amount' => 25000.0,
        'interest' => 8,
        'term_months' => 12,
        'starting_date' => new DateTime('2023-06-01'),
        'remaining_months' => 6,
    ];

    $loan = new LoanAmortization($loanData);

    // Get results multiple times
    $loan->getResults();
    $loan->getResults();
    $finalResults = $loan->getResults();

    // Verify the schedule is still correct
    $schedule = $finalResults['schedule'];

    expect(count($schedule))->toBe(12);

    $paid = array_filter($schedule, fn ($row) => $row[0] === 'paid');
    $notPaid = array_filter($schedule, fn ($row) => $row[0] === 'not_paid');

    expect(count($paid))->toBe(6)
        ->and(count($notPaid))->toBe(6);
});

// Edge case tests
it('handles single month loan', function () {
    $loanData = [
        'loan_amount' => 1000.0,
        'interest' => 12,
        'term_months' => 1,
        'starting_date' => new DateTime('2023-01-01'),
        'remaining_months' => 1,
    ];

    $loan = new LoanAmortization($loanData);
    $results = $loan->getResults();

    expect(count($results['schedule']))->toBe(1);
    expect($results['summary']['monthly_repayment'])->toBeGreaterThan(1000.0);
});

it('handles very long term loan (30 years)', function () {
    $loanData = [
        'loan_amount' => 300000.0,
        'interest' => 6.5,
        'term_months' => 360,
        'starting_date' => new DateTime('2023-01-01'),
        'remaining_months' => 360,
    ];

    $loan = new LoanAmortization($loanData);
    $results = $loan->getResults();

    expect(count($results['schedule']))->toBe(360);
    expect($results['summary']['monthly_repayment'])->toBeGreaterThan(0);
    expect($results['summary']['total_interest'])->toBeGreaterThan(100000); // Significant interest for 30 years
    expect($results['summary']['total_pay'])->toBeGreaterThan(300000.0);
});

it('handles very high interest rate', function () {
    $loanData = [
        'loan_amount' => 10000.0,
        'interest' => 50,
        'term_months' => 12,
        'starting_date' => new DateTime('2023-01-01'),
        'remaining_months' => 12,
    ];

    $loan = new LoanAmortization($loanData);
    $results = $loan->getResults();

    expect($results['summary']['monthly_repayment'])->toBeGreaterThan(833.33);
    expect($results['summary']['total_interest'])->toBeGreaterThan(0);
});

it('handles very small loan amount', function () {
    $loanData = [
        'loan_amount' => 100.0,
        'interest' => 5,
        'term_months' => 6,
        'starting_date' => new DateTime('2023-01-01'),
        'remaining_months' => 6,
    ];

    $loan = new LoanAmortization($loanData);
    $results = $loan->getResults();

    expect($results['summary']['monthly_repayment'])->toBeGreaterThan(0);
    expect($results['summary']['total_pay'])->toBeGreaterThan(100.0);
});

it('handles very large loan amount', function () {
    $loanData = [
        'loan_amount' => 10000000.0,
        'interest' => 7.5,
        'term_months' => 120,
        'starting_date' => new DateTime('2023-01-01'),
        'remaining_months' => 120,
    ];

    $loan = new LoanAmortization($loanData);
    $results = $loan->getResults();

    expect($results['summary']['monthly_repayment'])->toBeGreaterThan(0);
    expect($results['summary']['total_pay'])->toBeGreaterThan(10000000.0);
    expect(count($results['schedule']))->toBe(120);
});

it('handles remaining months equal to zero (fully paid loan)', function () {
    $loanData = [
        'loan_amount' => 5000.0,
        'interest' => 10,
        'term_months' => 12,
        'starting_date' => new DateTime('2023-01-01'),
        'remaining_months' => 0,
    ];

    $loan = new LoanAmortization($loanData);
    $results = $loan->getResults();

    $schedule = $results['schedule'];
    $paid = array_filter($schedule, fn ($row) => $row[0] === 'paid');

    expect(count($schedule))->toBe(12);
    expect(count($paid))->toBe(12);
});

it('handles remaining months equal to term months (no payments made)', function () {
    $loanData = [
        'loan_amount' => 8000.0,
        'interest' => 8,
        'term_months' => 18,
        'starting_date' => new DateTime('2023-01-01'),
        'remaining_months' => 18,
    ];

    $loan = new LoanAmortization($loanData);
    $results = $loan->getResults();

    $schedule = $results['schedule'];
    $notPaid = array_filter($schedule, fn ($row) => $row[0] === 'not_paid');

    expect(count($schedule))->toBe(18);
    expect(count($notPaid))->toBe(18);
});

it('calculates diminishing balance correctly over schedule', function () {
    $loanData = [
        'loan_amount' => 10000.0,
        'interest' => 12,
        'term_months' => 6,
        'starting_date' => new DateTime('2023-01-01'),
        'remaining_months' => 6,
    ];

    $loan = new LoanAmortization($loanData);
    $results = $loan->getResults();

    $schedule = $results['schedule'];
    $previousBalance = 10000.0;

    foreach ($schedule as $payment) {
        [, $details] = $payment;

        // Balance should decrease each month
        expect($details['balance'])->toBeLessThan($previousBalance);

        // Principal + Interest should equal payment
        expect($details['principal'] + $details['interest'])->toEqualWithDelta($details['payment'], 0.01);

        $previousBalance = $details['balance'];
    }

    // Final balance should be close to zero
    expect($schedule[count($schedule) - 1][1]['balance'])->toEqualWithDelta(0, 0.01);
});

it('handles zero term_months by defaulting to 1', function () {
    $loanData = [
        'loan_amount' => 5000.0,
        'interest' => 10,
        'term_months' => 0,
        'starting_date' => new DateTime('2023-01-01'),
        'remaining_months' => 1,
    ];

    $loan = new LoanAmortization($loanData);
    $results = $loan->getResults();

    // Should default to 1 month
    expect(count($results['schedule']))->toBe(1);
});

it('handles low interest rate (less than 1 percent)', function () {
    $loanData = [
        'loan_amount' => 20000.0,
        'interest' => 0.5,
        'term_months' => 24,
        'starting_date' => new DateTime('2023-01-01'),
        'remaining_months' => 24,
    ];

    $loan = new LoanAmortization($loanData);
    $results = $loan->getResults();

    expect($results['summary']['monthly_repayment'])->toBeGreaterThan(833.33);
    expect($results['summary']['total_interest'])->toBeGreaterThan(0);
    expect($results['summary']['total_interest'])->toBeLessThan(200);
});
