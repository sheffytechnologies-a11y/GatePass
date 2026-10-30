<template>
  <ion-app>
    <ion-router-outlet />
  </ion-app>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { IonApp, IonRouterOutlet } from '@ionic/vue'
import { pushNotifications } from '@/services/pushNotifications'

onMounted(() => {
  pushNotifications.initialize()
})
</script>
