'''
Starting point for Corey Mudd's programming challenge
'''
import argparse
from classes.work import Work

def main(args):
    show_columns = get_columns(args)
    do_work = Work(args.input, args.output, show_columns)
    do_work.input()
    do_work.output()

def get_columns(args):
    columns = [];
    if args.max_time_gap:
        columns.append('max_time_gap')
    if args.total_volume:
        columns.append('total_volume')
    if args.weighted_avg_price:
        columns.append('weighted_avg_price')
    if args.max_trade_price:
        columns.append('max_trade_price')
    return columns

def parse_args():
    parser = argparse.ArgumentParser(description=__doc__)

    parser.add_argument(
        '--input',
        nargs='?',
        default='-',
        type=argparse.FileType('r'),
        help='path to the input file (default: stdin)'
    )

    parser.add_argument(
        '--output',
        nargs='?',
        default='-',
        type=argparse.FileType('w'),
        help='path to the output file (default: stdout)'
    )

    parser.add_argument(
        '--max_time_gap',
        dest='max_time_gap',
        action='store_const',
        const='1',
        help='add max_time_gap column to output'
    )

    parser.add_argument(
        '--total_volume',
        dest='total_volume',
        action='store_const',
        const='2',
        help='add total volume column to output'
    )

    parser.add_argument(
        '--weighted_avg_price',
        dest='weighted_avg_price',
        action='store_const',
        const='3',
        help='add weighted average price column to output'
    )

    parser.add_argument(
        '--max_trade_price',
        dest='max_trade_price',
        action='store_const',
        const='4',
        help='add max trade price column to output'
    )

    return parser.parse_args()

if __name__ == '__main__':
    args = parse_args()
    main(args)
