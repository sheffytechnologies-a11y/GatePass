import type { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
  appId: 'com.checkpazz.app',
  appName: 'checkpazz',
  webDir: 'dist',
  server: {
    androidScheme: 'https'
  },
};

export default config;
