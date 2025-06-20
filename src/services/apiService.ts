import axios from 'axios';
import { WalletInfo, Transaction, BalanceHistoryPoint } from '../types/wallet';

// Laravel API Base URL - Update this to your Laravel server
const API_BASE_URL = 'http://localhost:8000/api';

class ApiService {
  private axiosInstance;

  constructor() {
    this.axiosInstance = axios.create({
      baseURL: API_BASE_URL,
      timeout: 30000,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    });

    // Add response interceptor for error handling
    this.axiosInstance.interceptors.response.use(
      (response) => response,
      (error) => {
        console.error('API Error:', error.response?.data || error.message);
        throw error;
      }
    );
  }

  async getWalletInfo(address: string, network: string = 'ethereum'): Promise<WalletInfo> {
    try {
      console.log(`Fetching wallet info from: ${API_BASE_URL}/wallet/${address}?network=${network}`);
      
      const response = await this.axiosInstance.get(`/wallet/${address}`, {
        params: { network }
      });

      if (response.data.success) {
        const data = response.data.data;
        
        // Transform API response to match frontend interface
        const walletInfo: WalletInfo = {
          address: data.address,
          balance: data.balance,
          balanceUSD: data.balance_usd,
          network: data.network,
          transactions: data.transactions.map((tx: any) => ({
            id: tx.id.toString(),
            hash: tx.hash,
            type: tx.type,
            amount: tx.amount,
            amountUSD: tx.amount_usd,
            to: tx.to_address,
            from: tx.from_address,
            timestamp: tx.block_timestamp,
            status: tx.status,
            gasUsed: tx.gas_used?.toString(),
            gasPrice: tx.gas_price?.toString()
          })),
          balanceHistory: []
        };

        return walletInfo;
      } else {
        throw new Error(response.data.message || 'Failed to fetch wallet information');
      }
    } catch (error) {
      console.error('Error fetching wallet info:', error);
      
      // Fallback to dummy data if API is not available
      if (axios.isAxiosError(error) && (error.code === 'ECONNREFUSED' || error.response?.status === 404)) {
        console.warn('Laravel API not available, using dummy data for demo');
        return this.getDummyWalletInfo(address);
      }
      
      throw new Error('Failed to fetch wallet information from blockchain');
    }
  }

  async refreshWalletData(address: string, network: string = 'ethereum'): Promise<WalletInfo> {
    try {
      console.log(`Refreshing wallet data: ${API_BASE_URL}/wallet/${address}/refresh`);
      
      const response = await this.axiosInstance.post(`/wallet/${address}/refresh`, {
        network
      });

      if (response.data.success) {
        return this.getWalletInfo(address, network);
      } else {
        throw new Error(response.data.message || 'Failed to refresh wallet data');
      }
    } catch (error) {
      console.error('Error refreshing wallet data:', error);
      throw new Error('Failed to refresh wallet data');
    }
  }

  async getWalletHistory(address: string, network: string = 'ethereum'): Promise<BalanceHistoryPoint[]> {
    try {
      const response = await this.axiosInstance.get(`/wallet/${address}/history`, {
        params: { network }
      });

      if (response.data.success) {
        // API now returns { date, balance, balance_usd }
        // which matches the BalanceHistoryPoint type.
        return response.data.data.map((point: any) => ({
          date: point.date,
          balance: point.balance,
          balanceUSD: point.balance_usd,
        }));
      } else {
        throw new Error(response.data.message || 'Failed to fetch wallet history');
      }
    } catch (error) {
      console.error('Error fetching wallet history:', error);
      return []; // Return empty array on error
    }
  }

  async getWalletStats(address: string, network: string = 'ethereum') {
    try {
      const response = await this.axiosInstance.get(`/wallet/${address}/stats`, {
        params: { network }
      });

      if (response.data.success) {
        return response.data.data;
      } else {
        throw new Error(response.data.message || 'Failed to fetch wallet stats');
      }
    } catch (error) {
      console.error('Error fetching wallet stats:', error);
      throw error;
    }
  }

  async logWalletView(address: string, network: string = 'ethereum'): Promise<void> {
    try {
      console.log(`Logging wallet view: ${API_BASE_URL}/wallet/log`);
      
      await this.axiosInstance.post('/wallet/log', {
        address,
        network,
        timestamp: new Date().toISOString()
      });
      
      console.log(`Wallet view logged for address: ${address}`);
    } catch (error) {
      console.error('Error logging wallet view:', error);
      // Don't throw error for logging failures
    }
  }

  async getSupportedNetworks() {
    try {
      const response = await this.axiosInstance.get('/networks');
      
      if (response.data.success) {
        return response.data.data;
      } else {
        throw new Error('Failed to fetch supported networks');
      }
    } catch (error) {
      console.error('Error fetching networks:', error);
      
      // Return default networks if API fails
      return [
        {
          id: 'ethereum',
          name: 'Ethereum Mainnet',
          chain_id: 1,
          symbol: 'ETH',
          explorer: 'https://etherscan.io'
        },
        {
          id: 'bsc',
          name: 'Binance Smart Chain',
          chain_id: 56,
          symbol: 'BNB',
          explorer: 'https://bscscan.com'
        }
      ];
    }
  }

  async checkApiHealth() {
    try {
      const response = await this.axiosInstance.get('/health');
      return response.data;
    } catch (error) {
      console.error('API health check failed:', error);
      return { status: 'error', message: 'API not available' };
    }
  }

  // Fallback dummy data for demo purposes
  private getDummyWalletInfo(address: string): WalletInfo {
    const transactions: Transaction[] = [
      {
        id: '1',
        hash: '0xab1234567890abcdef1234567890abcdef1234567890abcdef1234567890abcdef',
        type: 'received',
        amount: '0.5',
        amountUSD: 1250.50,
        to: address,
        from: '0x8ba1f109551bD432803012645Hac136c22C57154',
        timestamp: new Date(Date.now() - 2 * 60 * 60 * 1000).toISOString(),
        status: 'confirmed',
        gasUsed: '21000',
        gasPrice: '20'
      },
      {
        id: '2',
        hash: '0xcd2345678901cdef2345678901cdef2345678901cdef2345678901cdef234567',
        type: 'sent',
        amount: '0.25',
        amountUSD: 625.25,
        to: '0x1234567890123456789012345678901234567890',
        from: address,
        timestamp: new Date(Date.now() - 5 * 60 * 60 * 1000).toISOString(),
        status: 'confirmed',
        gasUsed: '21000',
        gasPrice: '18'
      },
      {
        id: '3',
        hash: '0xef3456789012ef3456789012ef3456789012ef3456789012ef3456789012ef34',
        type: 'received',
        amount: '1.2',
        amountUSD: 3001.20,
        to: address,
        from: '0x987654321098765432109876543210987654321',
        timestamp: new Date(Date.now() - 24 * 60 * 60 * 1000).toISOString(),
        status: 'confirmed',
        gasUsed: '21000',
        gasPrice: '22'
      },
      {
        id: '4',
        hash: '0x123456789abcdef123456789abcdef123456789abcdef123456789abcdef1234',
        type: 'sent',
        amount: '0.1',
        amountUSD: 250.10,
        to: '0xabcdef1234567890abcdef1234567890abcdef12',
        from: address,
        timestamp: new Date(Date.now() - 48 * 60 * 60 * 1000).toISOString(),
        status: 'confirmed',
        gasUsed: '21000',
        gasPrice: '15'
      },
      {
        id: '5',
        hash: '0x567890abcdef567890abcdef567890abcdef567890abcdef567890abcdef5678',
        type: 'received',
        amount: '2.0',
        amountUSD: 5002.00,
        to: address,
        from: '0xfedcba0987654321fedcba0987654321fedcba09',
        timestamp: new Date(Date.now() - 72 * 60 * 60 * 1000).toISOString(),
        status: 'confirmed',
        gasUsed: '21000',
        gasPrice: '25'
      }
    ];

    const balanceHistory: BalanceHistoryPoint[] = [];
    const currentDate = new Date();
    const ethPrice = 2501.00;

    for (let i = 29; i >= 0; i--) {
      const date = new Date(currentDate);
      date.setDate(date.getDate() - i);
      
      const baseBalance = 2.5847;
      const variation = (Math.random() - 0.5) * 0.5;
      const balance = Math.max(0.1, baseBalance + variation);
      
      balanceHistory.push({
        date: date.toISOString().split('T')[0],
        balance: Number(balance.toFixed(4)),
        balanceUSD: Number((balance * ethPrice).toFixed(2))
      });
    }

    return {
      address,
      balance: '2.5847',
      balanceUSD: 6464.23,
      network: 'Ethereum Mainnet',
      transactions,
      balanceHistory
    };
  }
}

export const apiService = new ApiService();