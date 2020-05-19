'''
Work class that will read the file and do all the calculations using iterator
'''

class Work:
    def __init__(self, input_file, output_file, columns = []):
        self.input_file = input_file
        self.output_file = output_file
        self.columns = columns
        self.stock_dict = {}
        self.unsorted_symbols = []

    def __iter__(self):
        return self

    def __next__(self):
        line = self.input_file.readline()
        if line:
            return self.next(line)
        else:
            raise StopIteration()

    def next(self, line):
        splitLine = line.strip().split(',')
        if len(splitLine) != 4:
            # ignore malformed lines
            return;
        timestamp = int(splitLine[0])
        symbol = splitLine[1]
        quantity = int(splitLine[2])
        price = int(splitLine[3])
        if symbol not in self.stock_dict:
            column_list = {}
            if 'max_time_gap' in self.columns:
                column_list['max_time_gap'] = (timestamp, 0)
            if 'total_volume' in self.columns:
                column_list['total_volume'] = quantity
            if 'weighted_avg_price' in self.columns:
                column_list['weighted_avg_price'] = (quantity, quantity*price, price)
            if 'max_trade_price' in self.columns:
                column_list['max_trade_price'] = price
            self.stock_dict[symbol] = column_list
            self.unsorted_symbols.append(symbol)
            return column_list
        else:
            stock = self.stock_dict[symbol]
            if 'max_time_gap' in stock:
                stock['max_time_gap'] = self.__set_max_time_gap(stock['max_time_gap'], timestamp)
            if 'total_volume' in stock:
                stock['total_volume'] = self.__set_total_volume(stock['total_volume'], quantity)
            if 'weighted_avg_price' in stock:
                stock['weighted_avg_price'] = self.__set_weighted_avg_price(stock['weighted_avg_price'], quantity, quantity*price)
            if 'max_trade_price' in stock:
                stock['max_trade_price'] = self.__set_max_trade_price(stock['max_trade_price'], price)
            return stock

    def __set_max_time_gap(self, max_time_gap_tuple, timestamp):
        earlier_timestamp = max_time_gap_tuple[0]
        max_time_gap = max_time_gap_tuple[1]
        time_gap = timestamp-earlier_timestamp
        if time_gap > max_time_gap:
            return (timestamp, time_gap)
        return (timestamp, max_time_gap)

    def __set_total_volume(self, quantity, add_quantity):
        return add_quantity + quantity

    def __set_weighted_avg_price(self, weighted_avg_price_tuple, new_quantity, new_total):
        previous_quantity = weighted_avg_price_tuple[0]
        previous_total = weighted_avg_price_tuple[1]
        total_quantity = previous_quantity + new_quantity
        total_total = previous_total + new_total
        new_avg = int(total_total / total_quantity)
        return (total_quantity, total_total, new_avg)

    def __set_max_trade_price(self, max_trade_price, new_price):
        if new_price > max_trade_price:
            return new_price
        return max_trade_price

    def input(self):
        for line in self:
            #print(line)
            continue

    def output(self):
        sorted_symbols = sorted(self.unsorted_symbols)
        for symbol in sorted_symbols:
            if symbol in self.stock_dict:
                stock = self.stock_dict[symbol]
                line = [symbol]
                if 'max_time_gap' in self.columns:
                    line.append(str(stock['max_time_gap'][1]))
                if 'total_volume' in self.columns:
                    line.append(str(stock['total_volume']))
                if 'weighted_avg_price' in self.columns:
                    line.append(str(stock['weighted_avg_price'][2]))
                if 'max_trade_price' in self.columns:
                    line.append(str(stock['max_trade_price']))
                print(','.join(line).strip(), file=self.output_file)
