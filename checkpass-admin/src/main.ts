import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import BrandSpinner from './components/BrandSpinner.vue'
import './styles/global.css'
import '@phosphor-icons/web/duotone'

const app = createApp(App)
app.component('BrandSpinner', BrandSpinner)
app.use(createPinia())
app.use(router)
app.mount('#app')
