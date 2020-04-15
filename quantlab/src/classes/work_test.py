'''
Unit test for work.py
'''
import unittest
from unittest.mock import patch
from work import Work

class TestWork(unittest.TestCase):
    def test_next_empty(self):
        work = Work('fake_input.csv', 'fake_output.csv')
        return_work = work.next('1,a,1,1')
        self.assertDictEqual(return_work, {})

    def test_next_max_time_gap(self):
        work = Work('fake_input.csv', 'fake_output.csv', ['max_time_gap'])
        return_work = work.next('1,a,1,1')
        self.assertDictEqual(return_work, {'max_time_gap': (1, 0)})

    def test_next_total_volume(self):
        work = Work('fake_input.csv', 'fake_output.csv', ['total_volume'])
        return_work = work.next('1,a,1,1')
        self.assertDictEqual(return_work, {'total_volume': 1})

    def test_next_weighted_avg_price(self):
        work = Work('fake_input.csv', 'fake_output.csv', ['weighted_avg_price'])
        return_work = work.next('1,a,1,1')
        self.assertDictEqual(return_work, {'weighted_avg_price': (1, 1, 1)})

    def test_next_max_trade_price(self):
        work = Work('fake_input.csv', 'fake_output.csv', ['max_trade_price'])
        return_work = work.next('1,a,1,1')
        self.assertDictEqual(return_work, {'max_trade_price': 1})

    def test_next_all(self):
        work = Work('fake_input.csv', 'fake_output.csv', ['max_time_gap', 'total_volume', 'weighted_avg_price', 'max_trade_price'])
        return_work = work.next('1,a,1,1')
        self.assertDictEqual(return_work, {
            'max_time_gap': (1, 0),
            'total_volume': 1,
            'weighted_avg_price': (1, 1, 1),
            'max_trade_price': 1
        })

    def test_next_max_time_gap_existing(self):
        work = Work('fake_input.csv', 'fake_output.csv', ['max_time_gap'])
        work.stock_dict['a'] = {'max_time_gap': (1, 0)}
        return_work = work.next('2,a,1,1')
        self.assertDictEqual(return_work, {'max_time_gap': (2, 1)})

    def test_next_max_time_gap_existing_not_max(self):
        work = Work('fake_input.csv', 'fake_output.csv', ['max_time_gap'])
        work.stock_dict['a'] = {'max_time_gap': (2, 2)}
        return_work = work.next('3,a,1,1')
        self.assertDictEqual(return_work, {'max_time_gap': (3, 2)})

    def test_next_total_volume_existing(self):
        work = Work('fake_input.csv', 'fake_output.csv', ['total_volume'])
        work.stock_dict['a'] = {'total_volume': 1}
        return_work = work.next('2,a,1,1')
        self.assertDictEqual(return_work, {'total_volume': 2})

    def test_next_weighted_avg_price_existing(self):
        work = Work('fake_input.csv', 'fake_output.csv', ['weighted_avg_price'])
        work.stock_dict['a'] = {'weighted_avg_price': (1, 1, 1)}
        return_work = work.next('2,a,1,1')
        self.assertDictEqual(return_work, {'weighted_avg_price': (2, 2, 1)})

    def test_next_weighted_avg_price_existing_fractional(self):
        work = Work('fake_input.csv', 'fake_output.csv', ['weighted_avg_price'])
        work.stock_dict['a'] = {'weighted_avg_price': (1, 2, 2)}
        return_work = work.next('2,a,1,1')
        self.assertDictEqual(return_work, {'weighted_avg_price': (2, 3, 1)})

    def test_next_max_trade_price_existing(self):
        work = Work('fake_input.csv', 'fake_output.csv', ['max_trade_price'])
        work.stock_dict['a'] = {'max_trade_price': 1}
        return_work = work.next('2,a,1,2')
        self.assertDictEqual(return_work, {'max_trade_price': 2})

    def test_next_max_trade_price_existing_equal(self):
        work = Work('fake_input.csv', 'fake_output.csv', ['max_trade_price'])
        work.stock_dict['a'] = {'max_trade_price': 1}
        return_work = work.next('2,a,1,1')
        self.assertDictEqual(return_work, {'max_trade_price': 1})

    def test_next_max_trade_price_existing_not_max(self):
        work = Work('fake_input.csv', 'fake_output.csv', ['max_trade_price'])
        work.stock_dict['a'] = {'max_trade_price': 2}
        return_work = work.next('2,a,1,1')
        self.assertDictEqual(return_work, {'max_trade_price': 2})

    def test_next_all_existing(self):
        work = Work('fake_input.csv', 'fake_output.csv', ['max_time_gap', 'total_volume', 'weighted_avg_price', 'max_trade_price'])
        work.stock_dict['a'] = {
            'max_time_gap': (1, 0),
            'total_volume': 1,
            'weighted_avg_price': (1, 1, 1),
            'max_trade_price': 1
        }
        return_work = work.next('2,a,1,1')
        self.assertDictEqual(return_work, {
            'max_time_gap': (2, 1),
            'total_volume': 2,
            'weighted_avg_price': (2, 2, 1),
            'max_trade_price': 1
        })
