'''
Unit test for main.py
'''
import unittest
from main import get_columns

class TestGetColumns(unittest.TestCase):
    def test_no_args(self):
        args = type('', (), {})()
        args.max_time_gap = None
        args.total_volume = None
        args.weighted_avg_price = None
        args.max_trade_price = None
        columns = get_columns(args)
        self.assertListEqual(columns, [])

    def test_max_time_gap(self):
        args = type('', (), {})()
        args.max_time_gap = 1
        args.total_volume = None
        args.weighted_avg_price = None
        args.max_trade_price = None
        columns = get_columns(args)
        self.assertListEqual(columns, ['max_time_gap'])

    def test_total_volume(self):
        args = type('', (), {})()
        args.max_time_gap = None
        args.total_volume = 2
        args.weighted_avg_price = None
        args.max_trade_price = None
        columns = get_columns(args)
        self.assertListEqual(columns, ['total_volume'])

    def test_weighted_avg_price(self):
        args = type('', (), {})()
        args.max_time_gap = None
        args.total_volume = None
        args.weighted_avg_price = 3
        args.max_trade_price = None
        columns = get_columns(args)
        self.assertListEqual(columns, ['weighted_avg_price'])

    def test_max_trade_price(self):
        args = type('', (), {})()
        args.max_time_gap = None
        args.total_volume = None
        args.weighted_avg_price = None
        args.max_trade_price = 4
        columns = get_columns(args)
        self.assertListEqual(columns, ['max_trade_price'])

    def test_all_args(self):
        args = type('', (), {})()
        args.max_time_gap = 1
        args.total_volume = 2
        args.weighted_avg_price = 3
        args.max_trade_price = 4
        columns = get_columns(args)
        self.assertListEqual(columns, ['max_time_gap', 'total_volume', 'weighted_avg_price', 'max_trade_price'])
