import { useState, useCallback } from 'react';
import { WalletConnectionState, WalletInfo } from '../types/wallet';
import { walletService } from '../services/walletService';
import { apiService } from '../services/apiService';

export const useWallet = () => {
  const [connectionState, setConnectionState] = useState<WalletConnectionState>({
    isConnected: false,
    address: null,
    isConnecting: false,
    error: null
  });

  const [walletInfo, setWalletInfo] = useState<WalletInfo | null>(null);
  const [isLoadingInfo, setIsLoadingInfo] = useState(false);

  const connectWallet = useCallback(async () => {
    setConnectionState(prev => ({
      ...prev,
      isConnecting: true,
      error: null
    }));

    try {
      const address = await walletService.connectWallet();
      setConnectionState({
        isConnected: true,
        address,
        isConnecting: false,
        error: null
      });

      // Log wallet connection
      await apiService.logWalletView(address);

      // Fetch wallet info
      setIsLoadingInfo(true);
      try {
        const info = await apiService.getWalletInfo(address);
        setWalletInfo(info);
      } catch (error) {
        console.error('Failed to fetch wallet info:', error);
      } finally {
        setIsLoadingInfo(false);
      }
    } catch (error) {
      setConnectionState({
        isConnected: false,
        address: null,
        isConnecting: false,
        error: error instanceof Error ? error.message : 'Failed to connect wallet'
      });
    }
  }, []);

  const disconnectWallet = useCallback(() => {
    setConnectionState({
      isConnected: false,
      address: null,
      isConnecting: false,
      error: null
    });
    setWalletInfo(null);
  }, []);

  const refreshWalletInfo = useCallback(async () => {
    if (!connectionState.address) return;

    setIsLoadingInfo(true);
    try {
      const info = await apiService.getWalletInfo(connectionState.address);
      setWalletInfo(info);
    } catch (error) {
      console.error('Failed to refresh wallet info:', error);
    } finally {
      setIsLoadingInfo(false);
    }
  }, [connectionState.address]);

  return {
    connectionState,
    walletInfo,
    isLoadingInfo,
    connectWallet,
    disconnectWallet,
    refreshWalletInfo
  };
};