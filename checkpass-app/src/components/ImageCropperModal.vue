<!-- src/components/ImageCropperModal.vue -->
<template>
  <ion-modal :is-open="isOpen" class="cropper-modal" @didDismiss="handleCancel">
    <div class="cropper-shell">
      <!-- Header -->
      <div class="cropper-header">
        <div>
          <h3 class="cropper-title">Crop Visitor Photo</h3>
          <p class="cropper-sub">Drag and zoom to frame the face</p>
        </div>
        <button type="button" class="cropper-close-btn" @click="handleCancel" aria-label="Cancel crop">✕</button>
      </div>

      <!-- Crop Viewport Stage -->
      <div class="cropper-stage" ref="stageRef" @pointerdown="onPointerDown" @pointermove="onPointerMove" @pointerup="onPointerUp" @pointercancel="onPointerUp">
        <!-- Interactive Image Canvas -->
        <canvas ref="displayCanvasRef" class="cropper-canvas"></canvas>

        <!-- Rule of Thirds Grid Overlay inside Crop Box -->
        <div class="crop-frame-guide" :style="cropBoxStyle">
          <div class="crop-grid">
            <div class="grid-line grid-v-1"></div>
            <div class="grid-line grid-v-2"></div>
            <div class="grid-line grid-h-1"></div>
            <div class="grid-line grid-h-2"></div>
          </div>
          <!-- Corner Markers -->
          <div class="crop-corner top-left"></div>
          <div class="crop-corner top-right"></div>
          <div class="crop-corner bottom-left"></div>
          <div class="crop-corner bottom-right"></div>
        </div>
      </div>

      <!-- Controls Row -->
      <div class="cropper-controls">
        <div class="zoom-control">
          <button type="button" class="zoom-step-btn" @click="stepZoom(-0.1)" title="Zoom out">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
              <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
          </button>
          <input
            type="range"
            v-model.number="scale"
            :min="minScale"
            :max="maxScale"
            step="0.02"
            class="zoom-slider"
            aria-label="Zoom scale"
          />
          <button type="button" class="zoom-step-btn" @click="stepZoom(0.1)" title="Zoom in">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
              <line x1="12" y1="5" x2="12" y2="19"></line>
              <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
          </button>
        </div>

        <div class="tool-buttons">
          <button type="button" class="tool-btn" @click="rotateImage">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
            </svg>
            <span>Rotate</span>
          </button>
          <button type="button" class="tool-btn" @click="resetTransforms">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/>
              <path d="M21 3v5h-5"/>
              <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/>
              <path d="M3 21v-5h5"/>
            </svg>
            <span>Reset</span>
          </button>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="cropper-actions">
        <button type="button" class="cropper-btn cropper-btn--cancel" @click="handleCancel">
          Cancel
        </button>
        <button type="button" class="cropper-btn cropper-btn--apply" @click="applyCrop">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"></polyline>
          </svg>
          <span>Apply Crop</span>
        </button>
      </div>
    </div>
  </ion-modal>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, nextTick } from 'vue'
import { IonModal } from '@ionic/vue'

const props = withDefaults(
  defineProps<{
    isOpen: boolean
    imageSrc: string
    aspectRatio?: number // default 1 (square)
    outputSize?: number  // default 512px
  }>(),
  {
    aspectRatio: 1,
    outputSize: 512,
  }
)

const emit = defineEmits<{
  (e: 'crop', dataUrl: string): void
  (e: 'cancel'): void
}>()

const stageRef = ref<HTMLDivElement | null>(null)
const displayCanvasRef = ref<HTMLCanvasElement | null>(null)

const loadedImage = ref<HTMLImageElement | null>(null)
const scale = ref(1)
const minScale = ref(0.6)
const maxScale = ref(3.5)
const panX = ref(0)
const panY = ref(0)
const rotation = ref(0) // degrees: 0, 90, 180, 270

// Crop frame dimensions (calculated based on stage size)
const cropBoxSize = ref(260)

const cropBoxStyle = computed(() => {
  const size = cropBoxSize.value
  return {
    width: `${size}px`,
    height: `${size / props.aspectRatio}px`,
    top: `calc(50% - ${size / (2 * props.aspectRatio)}px)`,
    left: `calc(50% - ${size / 2}px)`,
  }
})

// Drag tracking
let isDragging = false
let startPointerX = 0
let startPointerY = 0
let initialPanX = 0
let initialPanY = 0

function loadImage(src: string) {
  if (!src) return
  const img = new Image()
  img.crossOrigin = 'anonymous'
  img.onload = () => {
    loadedImage.value = img
    resetTransforms()
    drawCanvas()
  }
  img.src = src
}

watch(
  () => props.imageSrc,
  (newSrc) => {
    if (newSrc && props.isOpen) {
      loadImage(newSrc)
    }
  }
)

watch(
  () => props.isOpen,
  async (open) => {
    if (open) {
      await nextTick()
      initStage()
      if (props.imageSrc) {
        loadImage(props.imageSrc)
      }
    }
  }
)

function initStage() {
  if (!stageRef.value || !displayCanvasRef.value) return
  const rect = stageRef.value.getBoundingClientRect()
  const canvas = displayCanvasRef.value
  canvas.width = rect.width
  canvas.height = rect.height
  cropBoxSize.value = Math.min(rect.width - 40, rect.height - 40, 280)
}

function resetTransforms() {
  if (!loadedImage.value || !stageRef.value) return
  rotation.value = 0
  panX.value = 0
  panY.value = 0

  const imgW = loadedImage.value.naturalWidth || 400
  const imgH = loadedImage.value.naturalHeight || 400

  // Fit image to comfortably cover the crop box
  const targetSize = cropBoxSize.value
  const fitScale = Math.max(targetSize / imgW, targetSize / imgH)
  scale.value = Math.max(fitScale, 0.8)
  minScale.value = Math.max(fitScale * 0.7, 0.4)
  maxScale.value = Math.max(fitScale * 4, 3.5)

  drawCanvas()
}

function stepZoom(delta: number) {
  const next = scale.value + delta
  scale.value = Math.min(Math.max(next, minScale.value), maxScale.value)
  drawCanvas()
}

function rotateImage() {
  rotation.value = (rotation.value + 90) % 360
  drawCanvas()
}

watch([scale, rotation], () => {
  drawCanvas()
})

function drawCanvas() {
  const canvas = displayCanvasRef.value
  if (!canvas || !loadedImage.value) return
  const ctx = canvas.getContext('2d')
  if (!ctx) return

  const w = canvas.width
  const h = canvas.height
  ctx.clearRect(0, 0, w, h)

  const img = loadedImage.value
  ctx.save()

  // Move origin to canvas center + pan offset
  ctx.translate(w / 2 + panX.value, h / 2 + panY.value)
  ctx.rotate((rotation.value * Math.PI) / 180)
  ctx.scale(scale.value, scale.value)

  // Draw image centered
  ctx.drawImage(img, -img.naturalWidth / 2, -img.naturalHeight / 2)
  ctx.restore()

  // Draw darkened overlay outside crop box
  const boxW = cropBoxSize.value
  const boxH = cropBoxSize.value / props.aspectRatio
  const boxX = (w - boxW) / 2
  const boxY = (h - boxH) / 2

  ctx.save()
  ctx.fillStyle = 'rgba(10, 20, 15, 0.72)'
  // Top
  ctx.fillRect(0, 0, w, boxY)
  // Bottom
  ctx.fillRect(0, boxY + boxH, w, h - (boxY + boxH))
  // Left
  ctx.fillRect(0, boxY, boxX, boxH)
  // Right
  ctx.fillRect(boxX + boxW, boxY, w - (boxX + boxW), boxH)
  ctx.restore()
}

// ── Touch / Pointer Handling ──
function onPointerDown(e: PointerEvent) {
  isDragging = true
  startPointerX = e.clientX
  startPointerY = e.clientY
  initialPanX = panX.value
  initialPanY = panY.value
  ;(e.currentTarget as HTMLElement).setPointerCapture(e.pointerId)
}

function onPointerMove(e: PointerEvent) {
  if (!isDragging) return
  const dx = e.clientX - startPointerX
  const dy = e.clientY - startPointerY
  panX.value = initialPanX + dx
  panY.value = initialPanY + dy
  drawCanvas()
}

function onPointerUp(e: PointerEvent) {
  isDragging = false
  try {
    ;(e.currentTarget as HTMLElement).releasePointerCapture(e.pointerId)
  } catch {}
}

function applyCrop() {
  if (!loadedImage.value || !displayCanvasRef.value) return

  const outSize = props.outputSize
  const outCanvas = document.createElement('canvas')
  outCanvas.width = outSize
  outCanvas.height = outSize / props.aspectRatio
  const outCtx = outCanvas.getContext('2d')
  if (!outCtx) return

  outCtx.imageSmoothingEnabled = true
  outCtx.imageSmoothingQuality = 'high'

  const img = loadedImage.value
  const cropW = cropBoxSize.value
  const scaleRatio = outSize / cropW

  outCtx.save()
  // Translate to center of output canvas
  outCtx.translate(outSize / 2, (outSize / props.aspectRatio) / 2)
  // Apply pan relative to crop center scaled up to output size
  outCtx.translate(panX.value * scaleRatio, panY.value * scaleRatio)
  outCtx.rotate((rotation.value * Math.PI) / 180)
  outCtx.scale(scale.value * scaleRatio, scale.value * scaleRatio)

  outCtx.drawImage(img, -img.naturalWidth / 2, -img.naturalHeight / 2)
  outCtx.restore()

  const croppedDataUrl = outCanvas.toDataURL('image/jpeg', 0.88)
  emit('crop', croppedDataUrl)
}

function handleCancel() {
  emit('cancel')
}

onMounted(() => {
  window.addEventListener('resize', initStage)
})
</script>

<style scoped>
ion-modal.cropper-modal {
  --height: auto;
  --max-height: 94%;
  --border-radius: 24px;
  --backdrop-opacity: 0.75;
  align-items: center;
  justify-content: center;
}

.cropper-shell {
  background: #111A15;
  color: #FFFFFF;
  border-radius: 24px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  max-width: 440px;
  width: 100%;
  margin: auto;
}

/* Header */
.cropper-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px 12px;
  background: #15221B;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.cropper-title {
  margin: 0;
  font-size: 17px;
  font-weight: 700;
  color: #FFFFFF;
  font-family: var(--w-font-display, 'Bricolage Grotesque', sans-serif);
}

.cropper-sub {
  margin: 2px 0 0;
  font-size: 12px;
  color: #8E9F95;
}

.cropper-close-btn {
  background: rgba(255, 255, 255, 0.1);
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  color: #FFFFFF;
  font-size: 14px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.15s ease;
}

.cropper-close-btn:active {
  background: rgba(255, 255, 255, 0.2);
}

/* Stage */
.cropper-stage {
  position: relative;
  width: 100%;
  height: 320px;
  background: #0B120E;
  overflow: hidden;
  touch-action: none;
  user-select: none;
  cursor: grab;
}

.cropper-stage:active {
  cursor: grabbing;
}

.cropper-canvas {
  width: 100%;
  height: 100%;
  display: block;
}

/* Crop Frame Guide */
.crop-frame-guide {
  position: absolute;
  pointer-events: none;
  box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.8), 0 0 20px rgba(0, 0, 0, 0.5);
  border-radius: 12px;
  overflow: hidden;
}

.crop-grid {
  position: absolute;
  inset: 0;
}

.grid-line {
  position: absolute;
  background: rgba(255, 255, 255, 0.25);
}

.grid-v-1 { left: 33.33%; top: 0; bottom: 0; width: 1px; }
.grid-v-2 { left: 66.66%; top: 0; bottom: 0; width: 1px; }
.grid-h-1 { top: 33.33%; left: 0; right: 0; height: 1px; }
.grid-h-2 { top: 66.66%; left: 0; right: 0; height: 1px; }

/* Corner Handles */
.crop-corner {
  position: absolute;
  width: 14px;
  height: 14px;
  border-color: #00E58F;
  border-style: solid;
}

.crop-corner.top-left { top: 0; left: 0; border-width: 3px 0 0 3px; border-top-left-radius: 6px; }
.crop-corner.top-right { top: 0; right: 0; border-width: 3px 3px 0 0; border-top-right-radius: 6px; }
.crop-corner.bottom-left { bottom: 0; left: 0; border-width: 0 0 3px 3px; border-bottom-left-radius: 6px; }
.crop-corner.bottom-right { bottom: 0; right: 0; border-width: 0 3px 3px 0; border-bottom-right-radius: 6px; }

/* Controls */
.cropper-controls {
  padding: 14px 20px 10px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  background: #15221B;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.zoom-control {
  display: flex;
  align-items: center;
  gap: 12px;
}

.zoom-step-btn {
  background: rgba(255, 255, 255, 0.1);
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  color: #FFFFFF;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  flex-shrink: 0;
}

.zoom-step-btn:active {
  background: rgba(255, 255, 255, 0.2);
}

.zoom-slider {
  flex: 1;
  accent-color: #00E58F;
  height: 4px;
  cursor: pointer;
}

.tool-buttons {
  display: flex;
  justify-content: center;
  gap: 12px;
}

.tool-btn {
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #E2ECE5;
  font-size: 13px;
  font-weight: 600;
  border-radius: 10px;
  padding: 6px 14px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  transition: all 0.15s ease;
}

.tool-btn:active {
  background: rgba(255, 255, 255, 0.18);
}

/* Bottom Actions */
.cropper-actions {
  display: flex;
  gap: 12px;
  padding: 12px 20px 20px;
  background: #15221B;
}

.cropper-btn {
  flex: 1;
  height: 46px;
  border-radius: 14px;
  font-size: 15px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  cursor: pointer;
  border: none;
  transition: transform 0.15s ease;
}

.cropper-btn:active {
  transform: scale(0.98);
}

.cropper-btn--cancel {
  background: rgba(255, 255, 255, 0.08);
  color: #FFFFFF;
}

.cropper-btn--apply {
  background: #0A5C38;
  color: #FFFFFF;
  box-shadow: 0 4px 14px rgba(10, 92, 56, 0.4);
}
</style>

