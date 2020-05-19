# README

## Table of Contents

1. Setup
2. How to Run
3. How to Run Tests

## 1. Setup

* Install Python3.7.x from [python.org](http://python.org)
* Clone git repository from Github: [dunroamins/public](https://github.com/dunroamins/public.git)

## 2. How to Run

* `cd public/quantlab`
* `python src/main.py`
* optional arguments:
* --input [INPUT] - path to the input file (default: stdin)
* --output [OUTPUT] - path to the output file (default: stdout)
* --max_time_gap - add max_time_gap column to output
* --total_volume - add total volume column to output
* --weighted_avg_price - add weighted average price column to output
* --max_trade_price - add max trade price column to output
* To use supplied input.csv and create output.csv and retrieve all columns back:
* `python src/main.py --input data/input.csv --output data/output.csv --weighted_avg_price --max_time_gap --total_volume --max_trade_price`

## 3. How to Run Tests

* Run main_test.py
* `cd src`
* `python -m unittest main_test.py`

* Run work_test.py
* `cd classes`
* `python -m unittest work_test.py`

* TODO: Create test runner script to search directories and run all test files.
