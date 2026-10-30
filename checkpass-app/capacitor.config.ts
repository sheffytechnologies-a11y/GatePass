import type { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
  appId: 'com.checkpass.app',
  appName: 'Checkpass',
  webDir: 'dist',
  server: {
    androidScheme: 'https'
  },
};

export default config;
