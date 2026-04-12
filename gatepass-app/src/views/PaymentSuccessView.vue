<template>
  <ion-page>
    <ion-content :fullscreen="true" class="success-content">
      <div class="success-wrap">
        <div class="success-icon-wrap">
          <div class="success-icon-circle">
            <ion-icon :icon="checkmarkOutline" class="success-icon" />
          </div>
        </div>

        <div class="success-title">Payment received</div>
        <div class="success-body">
          We've received your payment and it's
          being reviewed. You'll get a notification
          once it's confirmed.
        </div>

        <button class="success-btn" @click="goNext">Continue</button>
      </div>
    </ion-content>
  </ion-page>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'
import { IonPage, IonContent, IonIcon } from '@ionic/vue'
import { checkmarkOutline } from 'ionicons/icons'

const router = useRouter()

function goNext() {
  router.push('/tabs/home')
}
</script>

<style scoped>
.success-content { --background: #fff; }
.success-wrap {
  min-height: 100%;
  padding: 26px 16px 18px;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.success-icon-wrap {
  margin-top: 12px;
  margin-bottom: 14px;
}
.success-icon-circle {
  width: 84px;
  height: 84px;
  border-radius: 50%;
  border: 6px solid #0b6c40;
  display: flex;
  align-items: center;
  justify-content: center;
}
.success-icon {
  font-size: 40px;
  color: #0b6c40;
}

.success-title {
  margin-top: 8px;
  color: #0b6c40;
  font-family: var(--w-font-display);
  font-size: 26px;
  line-height: 1.1;
  text-align: center;
  font-weight: 700;
}

.success-body {
  margin-top: 10px;
  color: var(--w-muted);
  text-align: center;
  font-size: 15px;
  line-height: 1.45;
  max-width: 290px;
  font-family: var(--w-font-body);
}

.success-btn {
  margin-top: auto;
  width: 100%;
  border: none;
  border-radius: 14px;
  background: #0b6c40;
  color: #fff;
  font-family: var(--w-font-body);
  font-size: 16px;
  line-height: 1;
  font-weight: 700;
  height: 52px;
  padding: 0 16px;
  box-shadow: 0 0 0 2px rgba(11, 108, 64, 0.2) inset;
}
</style>
