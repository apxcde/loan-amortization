<?php

namespace Apxcde\LoanAmortization;

class LoanAmortization
{
    private float $loan_amount;
    private int|float $interest;
    private int $term_months;
    private mixed $balance;
    private mixed $term_pay;
    private mixed $date;
    private mixed $remaining_months;
    public array $results;

    public function __construct(array $data)
    {
        $this->validate($data);

        $this->loan_amount = (float) $data['loan_amount'];
        $this->interest = (float) $data['interest'];
        $this->term_months = (int) $data['term_months'];
        $this->date = $data['starting_date'];
        $this->remaining_months = $data['remaining_months'];

        $this->term_months = ($this->term_months == 0) ? 1 : $this->term_months;

        $this->interest = ($this->interest / 12) / 100;

        $this->results = [
            'inputs' => $data,
            'summary' => $this->getSummary(),
            'schedule' => $this->getSchedule(),
        ];
    }

    public function getResults(): array
    {
        return $this->results;
    }

    private function validate(array $data): void
    {
        $requiredKeys = [
            'loan_amount',
            'interest',
            'term_months',
            'starting_date',
        ];

        $missing = array_diff($requiredKeys, array_keys($data));

        if (! empty($missing)) {
            throw new \InvalidArgumentException('Missing required keys: '.implode(', ', $missing));
        }
    }

    public function getSummary(): array
    {
        $this->calculate();
        $total_pay = $this->term_pay * $this->term_months;
        $total_interest = $total_pay - $this->loan_amount;

        return [
            'total_pay' => $total_pay,
            'total_interest' => $total_interest,
            'monthly_repayment' => $this->term_pay,
        ];
    }

    private function calculate(): array
    {
        if ($this->interest == 0.0) {
            $this->term_pay = $this->loan_amount / $this->term_months;
        } else {
            $this->term_pay = $this->loan_amount * ($this->interest / (1 - pow((1 + $this->interest), -$this->term_months)));
        }
        $interest = $this->loan_amount * $this->interest;

        $principal = $this->term_pay - $interest;
        $this->balance = $this->loan_amount - $principal;

        return [
            'payment' => $this->term_pay,
            'interest' => $interest,
            'principal' => $principal,
            'balance' => $this->balance,
            'date' => $this->date->format('Y-m-d'),
        ];
    }

    public function getSchedule(): array
    {
        $schedule = [];
        $totalMonths = $this->term_months;
        $monthsPaid = $totalMonths - $this->remaining_months;

        for ($month = 1; $month <= $totalMonths; $month++) {
            $this->date->modify('+1 month');

            if ($this->remaining_months === $this->term_months) {
                $status = 'not_paid';
            } elseif ($month <= $monthsPaid) {
                $status = 'paid';
            } else {
                $status = 'not_paid';
            }

            $schedule[] = [$status, $this->calculate()];
            $this->loan_amount = $this->balance;
        }

        return $schedule;
    }
}
