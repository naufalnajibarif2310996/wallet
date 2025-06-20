import React, { useEffect, useState } from 'react';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts';
import { useTheme } from '../contexts/ThemeContext';

interface PricePoint {
  date: string;
  price: number;
}

const CustomTooltip = ({ active, payload, label }: any) => {
  if (active && payload && payload.length) {
    const data = payload[0].payload;
    return (
      <div className="p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg">
        <p className="text-sm font-semibold text-gray-800 dark:text-gray-200">
          {label}
        </p>
        <p className="text-sm text-green-600 dark:text-green-400">
          Price: ${data.price.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
        </p>
      </div>
    );
  }
  return null;
};

export const BalanceChart: React.FC = () => {
  const { isDark } = useTheme();
  const [priceHistory, setPriceHistory] = useState<PricePoint[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function fetchPriceHistory() {
      setLoading(true);
      try {
        const res = await fetch('https://api.coingecko.com/api/v3/coins/ethereum/market_chart?vs_currency=usd&days=30');
        const data = await res.json();
        // data.prices: [[timestamp, price], ...]
        const history: PricePoint[] = data.prices.map((item: [number, number]) => ({
          date: new Date(item[0]).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }),
          price: item[1],
        }));
        setPriceHistory(history);
      } catch (e) {
        setPriceHistory([]);
      } finally {
        setLoading(false);
      }
    }
    fetchPriceHistory();
  }, []);

  const lineChartColor = isDark ? '#38bdf8' : '#0ea5e9';
  const gridColor = isDark ? '#4a5568' : '#e2e8f0';
  const textColor = isDark ? '#a0aec0' : '#4a5568';

  if (loading) {
    return <div className="flex items-center justify-center h-64">Loading chart...</div>;
  }

  if (!priceHistory || priceHistory.length === 0) {
    return (
      <div className="flex items-center justify-center h-64 bg-gray-100 dark:bg-gray-800 rounded-lg">
        <p className="text-gray-500">No price data available to display chart.</p>
      </div>
    );
  }

  return (
    <div className="p-4 bg-white dark:bg-gray-900 rounded-lg shadow-md transition-colors duration-300">
      <h3 className="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Ethereum Price History (USD)</h3>
      <ResponsiveContainer width="100%" height={300}>
        <LineChart 
          data={priceHistory}
          margin={{ top: 5, right: 20, left: 10, bottom: 5 }}
        >
          <CartesianGrid strokeDasharray="3 3" stroke={gridColor} />
          <XAxis 
            dataKey="date" 
            tick={{ fill: textColor, fontSize: 12 }} 
          />
          <YAxis 
            dataKey="price"
            tickFormatter={(value) => `$${Number(value).toLocaleString()}`}
            tick={{ fill: textColor, fontSize: 12 }}
            domain={['dataMin', 'dataMax']}
          />
          <Tooltip content={<CustomTooltip />} />
          <Line 
            type="monotone" 
            dataKey="price" 
            stroke={lineChartColor} 
            strokeWidth={2} 
            dot={false}
            activeDot={{ r: 6 }}
          />
        </LineChart>
      </ResponsiveContainer>
    </div>
  );
};