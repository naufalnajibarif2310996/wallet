declare global {
  interface Window {
    Pusher: any;
  }
}

import { useEffect } from 'react';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

export function useEthereumLiveTransactions(onNewTransaction: (tx: any) => void) {
  useEffect(() => {
    // Pastikan Pusher sudah diinstall dan key/cluster sudah benar
    window.Pusher = Pusher;
    const echo = new Echo({
      broadcaster: 'pusher',
      key: 'PUSHER_APP_KEY',
      cluster: 'PUSHER_APP_CLUSTER',
      forceTLS: true,
    });

    echo.channel('ethereum-transactions')
      .listen('.new-transaction', (e: any) => {
        console.log('Received from Pusher:', e.transaction);
        onNewTransaction(e.transaction);
      });

    return () => {
      echo.leave('ethereum-transactions');
    };
  }, [onNewTransaction]);
}
