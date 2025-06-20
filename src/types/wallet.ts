export interface WalletInfo {
  address: string;
  balance: string;
  balanceUSD: number;
  network: string;
  transactions: Transaction[];
  balanceHistory: BalanceHistoryPoint[];
}

export interface Transaction {
  id: string;
  hash: string;
  type: 'sent' | 'received';
  amount: string;
  amountUSD: number;
  to: string;
  from: string;
  timestamp: string;
  status: 'confirmed' | 'pending' | 'failed';
  gasUsed?: string;
  gasPrice?: string;
}

export interface BalanceHistoryPoint {
  date: string;
  balance: number;
  balanceUSD: number;
}

export interface WalletConnectionState {
  isConnected: boolean;
  address: string | null;
  isConnecting: boolean;
  error: string | null;
}