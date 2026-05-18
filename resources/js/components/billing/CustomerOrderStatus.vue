<template>
  <div class="min-h-screen bg-gray-100 flex flex-col items-center p-4">

    <!-- 🏪 BRAND HEADER -->
    <div class="w-full max-w-md bg-white rounded-3xl shadow p-4 flex items-center gap-3 mt-4">
      <img v-if="outlet.logo" :src="outlet.logo" class="w-12 h-12 rounded-xl object-cover border" />
      <div v-else class="w-12 h-12 rounded-xl border bg-gray-100 flex items-center justify-center text-lg font-bold">
        {{ outlet.name.charAt(0) }}
      </div>
      <div>
        <div class="font-semibold text-lg leading-tight">
          {{ outlet.name }}
        </div>
        <div class="text-xs text-gray-500">
          {{ outlet.location }}
        </div>
      </div>
    </div>

    <!-- 🔢 TOKEN -->
    <div class="w-full max-w-md bg-white rounded-3xl shadow p-6 text-center mt-4">
      <div class="text-xs text-gray-400">TOKEN</div>

      <div class="text-7xl font-extrabold tracking-widest text-black">
        {{ token }}
      </div>

      <!-- ORDER DETAILS -->
      <div class="mt-4 text-sm text-gray-600 space-y-1">
        <div><b>Type:</b> {{ order.type }}</div>
        <div><b>Items:</b> {{ order.items }}</div>
        <div><b>Total:</b> ₹{{ order.total }}</div>
      </div>
    </div>

    <!-- 📊 PROGRESS -->
    <div class="w-full max-w-md mt-6 bg-white rounded-3xl shadow p-6">

      <div class="relative mb-6">
        <div class="h-3 bg-gray-200 rounded-full overflow-hidden">
          <div
            class="h-3 rounded-full transition-all duration-700 animate-gradient"
            :style="{ width: progressWidth }"
          ></div>
        </div>
      </div>

      <!-- Steps -->
      <div class="flex justify-between">
        <div
          v-for="(step, i) in steps"
          :key="step.key"
          class="flex flex-col items-center w-1/4"
        >
          <div
            :class="[
              'w-10 h-10 flex items-center justify-center rounded-full text-xs font-bold transition-all duration-300',
              i === currentIndex
                ? 'bg-orange-500 text-white scale-125 animate-pulse shadow-lg'
                : i < currentIndex
                ? 'bg-green-500 text-white'
                : 'bg-gray-200 text-gray-500'
            ]"
          >
            {{ i + 1 }}
          </div>

          <div class="text-xs mt-2 text-center">
            {{ step.label }}
          </div>
        </div>
      </div>

      <!-- STATUS -->
      <transition name="fade-slide" mode="out-in">
        <div :key="currentStatus" class="mt-6 text-center">
          <div class="text-xl font-semibold">
            {{ statusMessage }}
          </div>
          <div class="text-sm text-gray-500 mt-1">
            {{ subMessage }}
          </div>
        </div>
      </transition>

    </div>

    <!-- 📷 QR SECTION (UPDATED ✅) -->
    <div class="w-full max-w-md mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">

      <!-- ✅ TOKEN QR (FULL WIDTH) -->
      <div
        v-if="tokenQr"
        class="w-full bg-white p-4 rounded-2xl shadow text-center sm:col-span-2"
      >
        <div class="text-xs text-gray-500 mb-2">Token QR</div>

        <div class="qr-box w-full flex justify-center" v-html="tokenQr"></div>

        <div class="text-[10px] text-gray-400 mt-1">
          Show to customer for scan
        </div>
        <div v-if="tokenUrl" class="text-[10px] text-blue-400 mt-1">
          <a :href="tokenUrl">Token Link</a>
        </div>
      </div>

      <!-- Invoice + kitchen QR -->
      <template v-else>

        <!-- Invoice QR -->
        <div class="w-full bg-white p-4 rounded-2xl shadow text-center">
          <div class="text-xs text-gray-500 mb-2">Invoice QR</div>
          <div class="qr-box w-full flex justify-center" v-html="qr"></div>
          <div class="text-[10px] text-gray-400 mt-1">Billing / Proof</div>
          <a
            v-if="invoiceUrl"
            :href="invoiceUrl"
            target="_blank"
            rel="noopener noreferrer"
            class="mt-2 block break-all text-[10px] font-semibold text-blue-500"
          >
            {{ invoiceUrl }}
          </a>
        </div>

        <!-- Kitchen QR -->
        <div class="w-full bg-white p-4 rounded-2xl shadow text-center">
          <div class="text-xs text-gray-500 mb-2">Kitchen Scan</div>
          <div class="qr-box w-full flex justify-center" v-html="kitchenQr"></div>
          <div class="text-[10px] text-gray-400 mt-1">Show to staff</div>
        </div>

      </template>

    </div>

    <!-- 🔊 SOUND -->
    <div class="mt-6">
      <button
        @click="toggleSound"
        class="px-4 py-2 rounded-xl border text-sm"
      >
        🔊 Sound: {{ soundEnabled ? 'ON' : 'OFF' }}
      </button>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, onBeforeUnmount } from 'vue'
import QRCode from 'qrcode'

// ✅ PROPS FROM PARENT
const props = defineProps({
  orderData: Object,
  tokenData: Object,
  qr: String,
  kitchenQr: String,
  tokenQr: String,
  tokenUrl: String,
  invoiceUrl: String
})

const getTenantInfo = () => {
  try {
    return JSON.parse(localStorage.getItem('tenant_info') || '{}')
  } catch (error) {
    return {}
  }
}

const outlet = computed(() => {
  const tenantInfo = getTenantInfo()
  const order = props.orderData || {}
  const branding = tenantInfo?.branding || {}
  const tenant = tenantInfo?.tenant || {}

  return {
    name: branding.company_name || order.branding?.company_name || tenant.name || order.tenant?.name || 'POS',
    location: branding.address || order.branding?.address || '',
    logo: branding.logo || order.branding?.logo || ''
  }
})

const liveToken = ref(props.tokenData || {})

// 🔢 TOKEN
const token = computed(() => liveToken.value?.token_code || '--')

// 📦 ORDER DETAILS
const order = computed(() => {
  const o = props.orderData || {}

  return {
    type: o.order_type || '-',
    total: o.total || 0,

    items: (o.items || [])
      .map(i => {
        const name = i.product?.name || i.product_name || 'Item'
        return `${name} x${i.quantity}`
      })
      .join(', ')
  }
})

// 🔄 FLOW
const steps = [
  { key: 'waiting', label: 'Received' },
  { key: 'pending', label: 'Queued' },
  { key: 'preparing', label: 'Cooking' },
  { key: 'ready', label: 'Ready' }
]

// 🔥 MAP BACKEND STATUS → UI STEP
const getStepIndex = (status) => {
  switch (status) {
    case 'waiting': return 0
    case 'pending': return 1
    case 'preparing': return 2
    case 'ready': return 3
    default: return 0
  }
}

// 🎯 CURRENT STEP (FROM API)
const currentIndex = ref(0)

watch(() => liveToken.value?.status, (newStatus) => {
  currentIndex.value = getStepIndex(newStatus)
}, { immediate: true })

const currentStatus = computed(() => steps[currentIndex.value].key)

// 📊 PROGRESS
const progressWidth = computed(() => {
  return `${(currentIndex.value / (steps.length - 1)) * 100}%`
})

// 💬 STATUS TEXT
const statusMessage = computed(() => ({
  waiting: 'Order Received',
  pending: 'Queued in Kitchen',
  preparing: 'Cooking in Progress',
  ready: 'Ready for Pickup 🎉'
}[currentStatus.value]))

const subMessage = computed(() => ({
  waiting: 'We got your order',
  pending: 'Chef will start soon',
  preparing: 'Almost there...',
  ready: 'Please collect your order'
}[currentStatus.value]))

// 🔊 SOUND FIX
const soundEnabled = ref(true)
let audioUnlocked = false

const unlockAudio = () => {
  if (audioUnlocked) return
  const temp = new Audio()
  temp.play().catch(() => {})
  audioUnlocked = true
}

window.addEventListener('click', unlockAudio)
window.addEventListener('touchstart', unlockAudio)

const playSound = () => {
  if (!soundEnabled.value || !audioUnlocked) return
  const audio = new Audio('https://actions.google.com/sounds/v1/alarms/beep_short.ogg')
  audio.play().catch(() => {})
}

// 🔁 Trigger sound on status change
watch(currentIndex, () => {
  playSound()
  if (navigator.vibrate) {
    navigator.vibrate([100, 50, 100])
  }
})

// 📷 QR
const invoiceQR = ref('')
const kitchenQR = ref('')

// 🔥 Generate QR from REAL DATA
onMounted(async () => {
  const orderId = props.orderData?.id
  const tokenCode = props.tokenData?.token_code

  invoiceQR.value = await QRCode.toDataURL(`invoice-${orderId}`)
  kitchenQR.value = await QRCode.toDataURL(`kitchen-${tokenCode}`)

  if (window.Echo) {
    window.Echo.channel('kitchen-orders')
    .listen('.order.status.updated', (data) => {
      if (String(data.order.id) === String(orderId)) {
        liveToken.value = data.token
      }
    })
  }
})

onBeforeUnmount(() => {
  if (window.Echo) {
    window.Echo.leave('kitchen-orders')
  }
})
</script>

<style>
.fade-slide-enter-active {
  transition: all 0.4s ease;
}
.fade-slide-enter-from {
  opacity: 0;
  transform: translateY(10px);
}
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

.animate-gradient {
  background: linear-gradient(270deg, #f59e0b, #ef4444, #22c55e, #3b82f6);
  background-size: 800% 800%;
  animation: gradientMove 4s ease infinite;
}

@keyframes gradientMove {
  0% { background-position: 0% 50% }
  50% { background-position: 100% 50% }
  100% { background-position: 0% 50% }
}

.qr-box {
    width: min(180px, 72vw);
    height: min(180px, 72vw);
    margin: 10px auto;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 12px;
    background: white;
    border-radius: 16px;
}

/* IMPORTANT: remove forced 90px */
.qr-box svg,
.qr-box img {
    width: 100% !important;
    height: 100% !important;
}

.qr-text {
    text-align: center;
    font-size: 11px;
    margin-top: 6px;
    clear: both;
}
</style>
