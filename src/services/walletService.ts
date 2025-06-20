import { ethers } from 'ethers';

declare global {
  interface Window {
    ethereum?: any;
  }
}

export class WalletService {
  private provider: ethers.BrowserProvider | null = null;
  private signer: ethers.JsonRpcSigner | null = null;

  async connectWallet(): Promise<string> {
    if (!window.ethereum) {
      // For demo purposes, return a dummy address if MetaMask is not available
      const dummyAddress = '0x742d35Cc6098AC1a6f27C17c5Ac6e3C48f8D3C2F';
      console.log('MetaMask not available, using dummy address for demo');
      return dummyAddress;
    }

    try {
      this.provider = new ethers.BrowserProvider(window.ethereum);
      await this.provider.send('eth_requestAccounts', []);
      this.signer = await this.provider.getSigner();
      const address = await this.signer.getAddress();
      return address;
    } catch (error) {
      console.error('Error connecting wallet:', error);
      throw new Error('Failed to connect wallet');
    }
  }

  async getBalance(address: string): Promise<string> {
    if (!this.provider) {
      // Return dummy balance for demo
      return '2.5847';
    }

    try {
      const balance = await this.provider.getBalance(address);
      return ethers.formatEther(balance);
    } catch (error) {
      console.error('Error getting balance:', error);
      return '0';
    }
  }

  async getNetwork(): Promise<string> {
    if (!this.provider) {
      return 'Ethereum Mainnet';
    }

    try {
      const network = await this.provider.getNetwork();
      return network.name;
    } catch (error) {
      console.error('Error getting network:', error);
      return 'Unknown';
    }
  }

  isWalletAvailable(): boolean {
    return typeof window !== 'undefined' && !!window.ethereum;
  }
}

export const walletService = new WalletService();