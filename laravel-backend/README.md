# Web3 Wallet Dashboard - Laravel Backend

Laravel backend API untuk Web3 Wallet Dashboard yang dapat terhubung langsung ke blockchain networks (Ethereum, BSC, Polygon).

## Features

- ✅ **Real Web3 Integration** - Koneksi langsung ke blockchain menggunakan web3.php
- ✅ **Multi-Network Support** - Ethereum, BSC, Polygon
- ✅ **Real-time Balance** - Fetch balance langsung dari blockchain
- ✅ **Transaction History** - Ambil transaksi dari blockchain explorer APIs
- ✅ **Price Integration** - Real-time token prices dari CoinGecko
- ✅ **Database Caching** - Cache data untuk performa optimal
- ✅ **RESTful API** - Clean API endpoints untuk frontend
- ✅ **CORS Support** - Ready untuk frontend integration

## Installation

### 1. Install Dependencies

```bash
cd laravel-backend
composer install
```

### 2. Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

### 3. Configure Database

Edit `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=web3_wallet_dashboard
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 4. Configure Web3 Services

Edit `.env` file dengan API keys:
```env
# Blockchain RPC URLs
ETHEREUM_RPC_URL=https://mainnet.infura.io/v3/YOUR_INFURA_PROJECT_ID
BSC_RPC_URL=https://bsc-dataseed.binance.org/
POLYGON_RPC_URL=https://polygon-rpc.com/

# Blockchain Explorer API Keys
ETHERSCAN_API_KEY=YOUR_ETHERSCAN_API_KEY
BSCSCAN_API_KEY=YOUR_BSCSCAN_API_KEY

# External APIs
MORALIS_API_KEY=YOUR_MORALIS_API_KEY
```

### 5. Run Migrations

```bash
php artisan migrate
```

### 6. Start Server

```bash
php artisan serve
```

Server akan berjalan di `http://localhost:8000`

## API Endpoints

### Get Wallet Info
```
GET /api/wallet/{address}?network=ethereum
```

Response:
```json
{
  "success": true,
  "data": {
    "address": "0x742d35Cc6098AC1a6f27C17c5Ac6e3C48f8D3C2F",
    "balance": "2.5847",
    "balance_usd": 6464.23,
    "network": "Ethereum Mainnet",
    "last_updated": "2024-01-15T10:30:00Z",
    "transactions": [...],
    "balance_history": [...]
  }
}
```

### Refresh Wallet Data
```
POST /api/wallet/{address}/refresh?network=ethereum
```

### Get Wallet Stats
```
GET /api/wallet/{address}/stats?network=ethereum
```

### Log Wallet View
```
POST /api/wallet/log
{
  "address": "0x742d35Cc6098AC1a6f27C17c5Ac6e3C48f8D3C2F",
  "network": "ethereum"
}
```

### Get Supported Networks
```
GET /api/networks
```

### Health Check
```
GET /api/health
```

## Supported Networks

- **Ethereum Mainnet** - Chain ID: 1
- **Binance Smart Chain** - Chain ID: 56  
- **Polygon** - Chain ID: 137

## Required API Keys

### 1. Infura (Ethereum RPC)
- Daftar di [infura.io](https://infura.io)
- Buat project baru
- Copy Project ID ke `ETHEREUM_RPC_URL`

### 2. Etherscan API
- Daftar di [etherscan.io](https://etherscan.io/apis)
- Generate API key
- Set `ETHERSCAN_API_KEY`

### 3. BSCScan API
- Daftar di [bscscan.com](https://bscscan.com/apis)
- Generate API key
- Set `BSCSCAN_API_KEY`

### 4. Moralis (Optional)
- Daftar di [moralis.io](https://moralis.io)
- Get API key
- Set `MORALIS_API_KEY`

## Database Schema

### Wallets Table
- `address` - Wallet address (primary key)
- `network` - Blockchain network
- `balance` - Current balance
- `balance_usd` - USD value
- `last_updated` - Last update timestamp

### Transactions Table
- `hash` - Transaction hash
- `type` - sent/received
- `amount` - Transaction amount
- `block_timestamp` - Block timestamp
- `status` - confirmed/pending/failed

### Balance History Table
- `wallet_address` - Foreign key
- `date` - Date
- `balance` - Balance on that date
- `balance_usd` - USD value

## Frontend Integration

Update frontend `apiService.ts`:

```typescript
const API_BASE_URL = 'http://localhost:8000/api';

async getWalletInfo(address: string): Promise<WalletInfo> {
  const response = await axios.get(`${API_BASE_URL}/wallet/${address}?network=ethereum`);
  return response.data.data;
}
```

## Production Deployment

1. Set `APP_ENV=production` in `.env`
2. Configure production database
3. Set up proper RPC endpoints (tidak menggunakan public RPC)
4. Enable caching: `php artisan config:cache`
5. Optimize: `php artisan optimize`

## Security Notes

- Semua API keys harus disimpan di `.env` file
- Jangan commit `.env` file ke repository
- Gunakan rate limiting untuk API endpoints
- Validate semua input addresses
- Monitor API usage untuk avoid rate limits

## Troubleshooting

### Web3 Connection Issues
- Pastikan RPC URL valid dan accessible
- Check API key limits
- Verify network connectivity

### Database Issues
- Run `php artisan migrate:fresh` untuk reset database
- Check database credentials di `.env`
- Ensure MySQL service running

### API Rate Limits
- Monitor API usage di dashboard provider
- Implement caching untuk reduce API calls
- Use multiple API keys untuk load balancing