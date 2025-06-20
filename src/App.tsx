import React from 'react';
import { ThemeProvider } from './contexts/ThemeContext';
import { ThemeToggle } from './components/ThemeToggle';
import { WalletConnection } from './components/WalletConnection';
import { WalletOverview } from './components/WalletOverview';
import { BalanceChart } from './components/BalanceChart';
import { TransactionHistory } from './components/TransactionHistory';
import { LoadingSpinner } from './components/LoadingSpinner';
import { useWallet } from './hooks/useWallet';
import { Activity } from 'lucide-react';

function AppContent() {
  const { connectionState, walletInfo, isLoadingInfo, connectWallet, disconnectWallet, refreshWalletInfo } = useWallet();

  return (
    <div className="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors duration-300">
      {/* Header */}
      <header className="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 transition-colors duration-300">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex items-center justify-between h-16">
            <div className="flex items-center gap-3">
              <div className="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                <Activity className="w-5 h-5 text-white" />
              </div>
              <h1 className="text-xl font-bold text-gray-900 dark:text-white">
                Web3 Wallet Dashboard
              </h1>
            </div>
            <ThemeToggle />
          </div>
        </div>
      </header>

      {/* Main Content */}
      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {!connectionState.isConnected ? (
          <div className="max-w-md mx-auto">
            <div className="text-center mb-8">
              <h2 className="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                Welcome to Web3 Dashboard
              </h2>
              <p className="text-gray-600 dark:text-gray-400">
                Connect your wallet to access your personalized dashboard with real-time balance tracking, transaction history, and analytics.
              </p>
            </div>
            <WalletConnection
              connectionState={connectionState}
              onConnect={connectWallet}
              onDisconnect={disconnectWallet}
            />
          </div>
        ) : (
          <div className="space-y-8">
            {/* Connection Status */}
            <WalletConnection
              connectionState={connectionState}
              onConnect={connectWallet}
              onDisconnect={disconnectWallet}
            />

            {isLoadingInfo ? (
              <LoadingSpinner message="Loading wallet information..." />
            ) : walletInfo ? (
              <>
                {/* Wallet Overview */}
                <WalletOverview
                  walletInfo={walletInfo}
                  isLoading={isLoadingInfo}
                  onRefresh={refreshWalletInfo}
                />

                {/* Charts and Transactions Grid */}
                <div className="grid grid-cols-1 xl:grid-cols-3 gap-8">
                  <div className="xl:col-span-2">
                    <BalanceChart data={walletInfo.balanceHistory} />
                  </div>
                  <div className="xl:col-span-1">
                    <TransactionHistory transactions={walletInfo.transactions} />
                  </div>
                </div>
              </>
            ) : (
              <div className="text-center py-12">
                <p className="text-gray-600 dark:text-gray-400">
                  Failed to load wallet information. Please try refreshing.
                </p>
              </div>
            )}
          </div>
        )}
      </main>

      {/* Footer */}
      <footer className="bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 mt-16">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
          <div className="text-center text-sm text-gray-600 dark:text-gray-400">
            <p>Web3 Wallet Dashboard - Built with React, Tailwind CSS, and ethers.js</p>
            <p className="mt-1">Backend powered by Laravel REST API</p>
          </div>
        </div>
      </footer>
    </div>
  );
}

function App() {
  return (
    <ThemeProvider>
      <AppContent />
    </ThemeProvider>
  );
}

export default App;