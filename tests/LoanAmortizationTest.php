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
