import React from 'react';
import { Wallet, TrendingUp, RefreshCw, Copy, ExternalLink } from 'lucide-react';
import { WalletInfo } from '../types/wallet';

interface WalletOverviewProps {
  walletInfo: WalletInfo;
  isLoading: boolean;
  onRefresh: () => void;
}

export const WalletOverview: React.FC<WalletOverviewProps> = ({
  walletInfo,
  isLoading,
  onRefresh
}) => {
  const copyAddress = () => {
    navigator.clipboard.writeText(walletInfo.address);
  };

  const openInExplorer = () => {
    window.open(`https://etherscan.io/address/${walletInfo.address}`, '_blank');
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div className="flex items-center gap-3">
          <div className="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
            <Wallet className="w-6 h-6 text-white" />
          </div>
          <div>
            <h2 className="text-xl font-bold text-gray-900 dark:text-white">Wallet Overview</h2>
            <p className="text-sm text-gray-600 dark:text-gray-400">{walletInfo.network}</p>
          </div>
        </div>
        <button
          onClick={onRefresh}
          disabled={isLoading}
          className="p-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors disabled:animate-spin"
        >
          <RefreshCw className="w-5 h-5" />
        </button>
      </div>

      {/* Address */}
      <div className="p-4 bg-gray-50 dark:bg-gray-800 rounded-xl">
        <div className="flex items-center justify-between">
          <div>
            <p className="text-sm font-medium text-gray-700 dark:text-gray-300">Wallet Address</p>
            <p className="text-lg font-mono text-gray-900 dark:text-white mt-1">
              {walletInfo.address.slice(0, 12)}...{walletInfo.address.slice(-8)}
            </p>
          </div>
          <div className="flex gap-2">
            <button
              onClick={copyAddress}
              className="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors"
              title="Copy address"
            >
              <Copy className="w-4 h-4" />
            </button>
            <button
              onClick={openInExplorer}
              className="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors"
              title="View on Etherscan"
            >
              <ExternalLink className="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>

      {/* Balance Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div className="p-6 bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 border border-blue-100 dark:border-blue-800 rounded-xl">
          <div className="flex items-center justify-between mb-4">
            <p className="text-sm font-medium text-blue-700 dark:text-blue-300">ETH Balance</p>
            <TrendingUp className="w-5 h-5 text-blue-600 dark:text-blue-400" />
          </div>
          <p className="text-3xl font-bold text-blue-900 dark:text-blue-100">
            {walletInfo.balance} ETH
          </p>
        </div>

        <div className="p-6 bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border border-green-100 dark:border-green-800 rounded-xl">
          <div className="flex items-center justify-between mb-4">
            <p className="text-sm font-medium text-green-700 dark:text-green-300">USD Value</p>
            <TrendingUp className="w-5 h-5 text-green-600 dark:text-green-400" />
          </div>
          <p className="text-3xl font-bold text-green-900 dark:text-green-100">
            ${walletInfo.balanceUSD.toLocaleString()}
          </p>
        </div>
      </div>
    </div>
  );
};