<template>
  <div v-if="modelValue" class="cropper-backdrop" @click.self="cancel">
    <div class="cropper-card" role="dialog" aria-modal="true" :aria-label="title">
      <!-- Header -->
      <div class="cropper-header">
        <div class="cropper-header-info">
          <div class="cropper-eyebrow">IMAGE ADJUSTMENT</div>
          <h3 class="cropper-title">{{ title }}</h3>
        </div>
        <button type="button" class="cropper-close-btn" @click="cancel" title="Close">✕</button>
      </div>

      <!-- Canvas Viewport -->
      <div class="cropper-body">
        <div
          ref="containerRef"
          class="canvas-container"
          @mousedown="onMouseDown"
          @touchstart="onTouchStart"
        >
          <canvas ref="canvasRef" class="crop-canvas"></canvas>
          <div class="drag-hint">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="5 9 2 12 5 15"/>
              <polyline points="9 5 12 2 15 5"/>
              <polyline points="15 19 12 22 9 19"/>
              <polyline points="19 9 22 12 19 15"/>
              <line x1="2" y1="12" x2="22" y2="12"/>
              <line x1="12" y1="2" x2="12" y2="22"/>
            </svg>
            <span>Drag to reposition</span>
          </div>
        </div>

        <!-- Controls Toolbar -->
        <div class="cropper-controls">
          <!-- Zoom slider -->
          <div class="control-row">
            <span class="control-label">Zoom</span>
            <button type="button" class="btn-ctrl" @click="adjustZoom(-0.15)" title="Zoom Out">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="8"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
            </button>
            <input
              type="range"
              min="1"
              max="3"
              step="0.02"
              v-model.number="zoom"
              class="zoom-slider"
              @input="draw"
            />
            <button type="button" class="btn-ctrl" @click="adjustZoom(0.15)" title="Zoom In">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="8"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
            </button>
          </div>

          <!-- Rotation & Reset -->
          <div class="tool-buttons">
            <button type="button" class="btn-tool" @click="rotateRight" title="Rotate 90 degrees">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <polyline points="23 4 23 10 17 10"/>
                <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
              </svg>
              <span>Rotate 90°</span>
            </button>
            <button type="button" class="btn-tool" @click="resetTransforms" title="Reset positioning">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                <polyline points="3 3 3 8 8 8"/>
              </svg>
              <span>Reset</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Footer Actions -->
      <div class="cropper-footer">
        <button type="button" class="btn-cancel" @click="cancel">Cancel</button>
        <button type="button" class="btn-apply" @click="applyCrop">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
          <span>Crop & Apply</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted, nextTick } from 'vue'

const props = withDefaults(
  defineProps<{
    modelValue: boolean
    imageSrc: string
    title?: string
    aspectRatio?: number
    circular?: boolean
    outputSize?: number
  }>(),
  {
    title: 'Crop & Position Photo',
    aspectRatio: 1,
    circular: true,
    outputSize: 512,
  }
)

const emit = defineEmits<{
  (e: 'update:modelValue', val: boolean): void
  (e: 'crop', croppedDataUrl: string): void
  (e: 'cancel'): void
}>()

const canvasRef = ref<HTMLCanvasElement | null>(null)
const containerRef = ref<HTMLDivElement | null>(null)

let img: HTMLImageElement | null = null
let canvasWidth = 360
let canvasHeight = 360
let baseScale = 1

const zoom = ref(1)
const panX = ref(0)
const panY = ref(0)
const rotation = ref(0) // degrees: 0, 90, 180, 270

// Drag state
let isDragging = false
let dragStartX = 0
let dragStartY = 0
let panStartX = 0
let panStartY = 0

function getRadius(): number {
  return Math.min(canvasWidth, canvasHeight) * 0.38
}

function initImage() {
  if (!props.imageSrc) return
  img = new Image()
  img.crossOrigin = 'anonymous'
  img.onload = () => {
    resetTransforms()
    nextTick(() => {
      setupCanvas()
      draw()
    })
  }
  img.src = props.imageSrc
}

function setupCanvas() {
  if (!canvasRef.value || !containerRef.value) return
  const rect = containerRef.value.getBoundingClientRect()
  canvasWidth = Math.round(rect.width) || 360
  canvasHeight = Math.round(rect.height) || 360

  const dpr = window.devicePixelRatio || 1
  canvasRef.value.width = canvasWidth * dpr
  canvasRef.value.height = canvasHeight * dpr
  canvasRef.value.style.width = `${canvasWidth}px`
  canvasRef.value.style.height = `${canvasHeight}px`

  if (!img) return
  const r = getRadius()
  const targetDiam = r * 2
  // Scale so min dimension fills targetDiam
  const minDim = Math.min(img.naturalWidth, img.naturalHeight) || 1
  baseScale = targetDiam / minDim
}

function resetTransforms() {
  zoom.value = 1
  panX.value = 0
  panY.value = 0
  rotation.value = 0
  if (img) {
    const r = getRadius()
    const minDim = Math.min(img.naturalWidth, img.naturalHeight) || 1
    baseScale = (r * 2) / minDim
  }
  draw()
}

function adjustZoom(delta: number) {
  zoom.value = Math.min(3, Math.max(1, Number((zoom.value + delta).toFixed(2))))
  draw()
}

function rotateRight() {
  rotation.value = (rotation.value + 90) % 360
  draw()
}

function draw() {
  if (!canvasRef.value || !img || !img.complete) return
  const ctx = canvasRef.value.getContext('2d')
  if (!ctx) return

  const dpr = window.devicePixelRatio || 1
  ctx.save()
  ctx.clearRect(0, 0, canvasWidth * dpr, canvasHeight * dpr)
  ctx.scale(dpr, dpr)

  const cx = canvasWidth / 2
  const cy = canvasHeight / 2
  const r = getRadius()

  // 1. Draw Image with transforms
  ctx.save()
  ctx.translate(cx + panX.value, cy + panY.value)
  ctx.rotate((rotation.value * Math.PI) / 180)
  const currentScale = baseScale * zoom.value
  ctx.scale(currentScale, currentScale)
  ctx.drawImage(img, -img.naturalWidth / 2, -img.naturalHeight / 2)
  ctx.restore()

  // 2. Draw Mask Overlay (Dark outside crop zone)
  ctx.save()
  ctx.fillStyle = 'rgba(10, 15, 20, 0.65)'
  ctx.fillRect(0, 0, canvasWidth, canvasHeight)

  // Cut out the crop area
  ctx.globalCompositeOperation = 'destination-out'
  ctx.beginPath()
  if (props.circular) {
    ctx.arc(cx, cy, r, 0, Math.PI * 2)
  } else {
    ctx.rect(cx - r, cy - r, r * 2, r * 2)
  }
  ctx.fill()
  ctx.restore()

  // 3. Draw Crop Outline & Grid lines
  ctx.save()
  ctx.beginPath()
  if (props.circular) {
    ctx.arc(cx, cy, r, 0, Math.PI * 2)
  } else {
    ctx.rect(cx - r, cy - r, r * 2, r * 2)
  }
  ctx.lineWidth = 2
  ctx.strokeStyle = '#10B981'
  ctx.stroke()

  // Subtle crosshairs/grid
  ctx.strokeStyle = 'rgba(255, 255, 255, 0.25)'
  ctx.lineWidth = 1
  ctx.setLineDash([4, 4])
  ctx.beginPath()
  // Vertical third lines
  ctx.moveTo(cx - r / 3, cy - r * 0.9)
  ctx.lineTo(cx - r / 3, cy + r * 0.9)
  ctx.moveTo(cx + r / 3, cy - r * 0.9)
  ctx.lineTo(cx + r / 3, cy + r * 0.9)
  // Horizontal third lines
  ctx.moveTo(cx - r * 0.9, cy - r / 3)
  ctx.lineTo(cx + r * 0.9, cy - r / 3)
  ctx.moveTo(cx - r * 0.9, cy + r / 3)
  ctx.lineTo(cx + r * 0.9, cy + r / 3)
  ctx.stroke()
  ctx.restore()

  ctx.restore()
}

// Mouse dragging
function onMouseDown(e: MouseEvent) {
  isDragging = true
  dragStartX = e.clientX
  dragStartY = e.clientY
  panStartX = panX.value
  panStartY = panY.value
  window.addEventListener('mousemove', onMouseMove)
  window.addEventListener('mouseup', onMouseUp)
}

function onMouseMove(e: MouseEvent) {
  if (!isDragging) return
  panX.value = panStartX + (e.clientX - dragStartX)
  panY.value = panStartY + (e.clientY - dragStartY)
  draw()
}

function onMouseUp() {
  isDragging = false
  window.removeEventListener('mousemove', onMouseMove)
  window.removeEventListener('mouseup', onMouseUp)
}

// Touch dragging
function onTouchStart(e: TouchEvent) {
  if (e.touches.length !== 1) return
  isDragging = true
  dragStartX = e.touches[0].clientX
  dragStartY = e.touches[0].clientY
  panStartX = panX.value
  panStartY = panY.value
  window.addEventListener('touchmove', onTouchMove, { passive: false })
  window.addEventListener('touchend', onTouchEnd)
}

function onTouchMove(e: TouchEvent) {
  if (!isDragging || e.touches.length !== 1) return
  e.preventDefault()
  panX.value = panStartX + (e.touches[0].clientX - dragStartX)
  panY.value = panStartY + (e.touches[0].clientY - dragStartY)
  draw()
}

function onTouchEnd() {
  isDragging = false
  window.removeEventListener('touchmove', onTouchMove)
  window.removeEventListener('touchend', onTouchEnd)
}

function cancel() {
  emit('update:modelValue', false)
  emit('cancel')
}

function applyCrop() {
  if (!img) return
  const outCanvas = document.createElement('canvas')
  const outSize = props.outputSize
  outCanvas.width = outSize
  outCanvas.height = outSize
  const outCtx = outCanvas.getContext('2d')
  if (!outCtx) return

  const r = getRadius()
  const factor = outSize / (r * 2)

  outCtx.save()
  // Move origin to center of outCanvas
  outCtx.translate(outSize / 2, outSize / 2)
  // Apply pan shifted by scale factor
  outCtx.translate(panX.value * factor, panY.value * factor)
  // Apply rotation
  outCtx.rotate((rotation.value * Math.PI) / 180)
  // Apply scale
  const finalScale = baseScale * zoom.value * factor
  outCtx.scale(finalScale, finalScale)
  outCtx.drawImage(img, -img.naturalWidth / 2, -img.naturalHeight / 2)
  outCtx.restore()

  const croppedDataUrl = outCanvas.toDataURL('image/jpeg', 0.92)
  emit('crop', croppedDataUrl)
  emit('update:modelValue', false)
}

watch(
  () => props.modelValue,
  (val) => {
    if (val) {
      nextTick(() => {
        initImage()
      })
    }
  }
)

watch(() => props.imageSrc, () => {
  if (props.modelValue) initImage()
})

onMounted(() => {
  if (props.modelValue) initImage()
})

onUnmounted(() => {
  window.removeEventListener('mousemove', onMouseMove)
  window.removeEventListener('mouseup', onMouseUp)
  window.removeEventListener('touchmove', onTouchMove)
  window.removeEventListener('touchend', onTouchEnd)
})
</script>

<style scoped>
.cropper-backdrop {
  position: fixed;
  inset: 0;
  z-index: 9999;
  background: rgba(10, 17, 13, 0.78);
  backdrop-filter: blur(6px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
  animation: fadeIn 0.2s ease-out;
}

.cropper-card {
  background: #ffffff;
  border-radius: 20px;
  width: 100%;
  max-width: 440px;
  box-shadow: 0 20px 45px rgba(0, 0, 0, 0.35);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.cropper-header {
  padding: 16px 20px;
  border-bottom: 1px solid #F3F4F6;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.cropper-eyebrow {
  font-size: 10.5px;
  font-weight: 700;
  letter-spacing: 0.12em;
  color: #0A5C38;
  text-transform: uppercase;
}

.cropper-title {
  margin: 2px 0 0;
  font-size: 16px;
  font-weight: 700;
  color: #111827;
}

.cropper-close-btn {
  background: #F3F4F6;
  border: none;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  color: #6B7280;
  font-size: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.15s ease;
}

.cropper-close-btn:hover {
  background: #E5E7EB;
  color: #111827;
}

.cropper-body {
  padding: 16px 20px;
  display: flex;
  flex-direction: column;
  gap: 14px;
  align-items: center;
}

.canvas-container {
  width: 360px;
  height: 340px;
  max-width: 100%;
  position: relative;
  background: #0F172A;
  border-radius: 16px;
  overflow: hidden;
  cursor: grab;
  touch-action: none;
  box-shadow: inset 0 2px 8px rgba(0, 0, 0, 0.4);
}

.canvas-container:active {
  cursor: grabbing;
}

.crop-canvas {
  width: 100%;
  height: 100%;
  display: block;
}

.drag-hint {
  position: absolute;
  bottom: 10px;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(0, 0, 0, 0.65);
  color: #F3F4F6;
  font-size: 11px;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 20px;
  display: flex;
  align-items: center;
  gap: 5px;
  pointer-events: none;
  user-select: none;
}

.cropper-controls {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.control-row {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #F9FAFB;
  padding: 8px 12px;
  border-radius: 12px;
  border: 1px solid #E5E7EB;
}

.control-label {
  font-size: 12px;
  font-weight: 700;
  color: #4B5563;
  min-width: 36px;
}

.btn-ctrl {
  background: white;
  border: 1px solid #D1D5DB;
  border-radius: 8px;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #374151;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-ctrl:hover {
  border-color: #0A5C38;
  color: #0A5C38;
}

.zoom-slider {
  flex: 1;
  accent-color: #0A5C38;
  cursor: pointer;
}

.tool-buttons {
  display: flex;
  gap: 8px;
}

.btn-tool {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  background: #F9FAFB;
  border: 1px solid #E5E7EB;
  border-radius: 10px;
  padding: 8px 12px;
  font-size: 12px;
  font-weight: 600;
  color: #374151;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-tool:hover {
  background: #F3F4F6;
  border-color: #D1D5DB;
}

.cropper-footer {
  padding: 14px 20px;
  border-top: 1px solid #F3F4F6;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  background: #FAFAFA;
}

.btn-cancel {
  background: white;
  border: 1px solid #D1D5DB;
  border-radius: 10px;
  padding: 9px 16px;
  font-size: 13.5px;
  font-weight: 600;
  color: #4B5563;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-cancel:hover {
  background: #F3F4F6;
}

.btn-apply {
  background: #0A5C38;
  color: white;
  border: none;
  border-radius: 10px;
  padding: 9px 20px;
  font-size: 13.5px;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  transition: all 0.15s ease;
  box-shadow: 0 2px 6px rgba(10, 92, 56, 0.25);
}

.btn-apply:hover {
  background: #08492c;
  transform: translateY(-1px);
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
</style>
