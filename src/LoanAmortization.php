<?php

namespace Apxcde\LoanAmortization;

class LoanAmortization
{
    private float $loan_amount;
    private mixed $term_years;
    private int|float $interest;
    private int $term_months;
    private string $currency = "XXX";
    private mixed $balance;
    private mixed $term_pay;
    private mixed $date;
    private mixed $remaining_months;
    public array $results;

    public function __construct($data)
    {
        if ($this->validate($data)) {
            $this->loan_amount = (float) $data['loan_amount'];
            $this->term_years = (int) $data['term_years'];
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

            return $this->results;
        }
    }

    public function getResults(): array
    {
        return $this->results;
    }

    private function validate($data): bool
    {
        $data_format = [
            'loan_amount' => 0,
            'term_years' => 0,
            'interest' => 0,
            'term_months' => 0,
            'starting_date' => '',
        ];

        $validate_data = array_diff_key($data_format, $data);

        if (empty($validate_data)) {
            return true;
        } else {
            echo "<div style='background-color:#ccc;padding:0.5em;'>";
            echo '<p style="color: red; margin:0.5em 0em; font-weight: bold; background-color: #fff; padding: 0.2em;">Missing Values</p>';
            foreach ($validate_data as $key => $value) {
                echo ":: Value <b>$key</b> is missing.<br>";
            }
            echo "</div>";

            return false;
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
        $this->term_pay = $this->loan_amount * ($this->interest / (1 - pow((1 + $this->interest), -$this->term_months)));
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

        if ($this->remaining_months === $this->term_months) {
            $i = 1;
            while ($i <= $this->term_months) {
                $this->date->modify('+1 month');
                $schedule[] = ['not_paid', $this->calculate()];
                $this->loan_amount = $this->balance;
                $this->term_months--;
            }
        } else {
            $paid_months = $this->term_months - $this->remaining_months;
            $i = 1;
            while ($i <= $this->term_months) {
                $this->date->modify('+1 month');

                if ($this->term_months > $this->remaining_months) {
                    $schedule[] = ['paid', $this->calculate()];
                    $this->loan_amount = $this->balance;
                } else {
                    $schedule[] = ['not_paid', $this->calculate()];
                    $this->loan_amount = $this->balance;
                }

                $this->term_months--;
            }
        }

        return $schedule;
    }
}
