<template>
  <div class="page">

    <!-- Loader -->
    <div v-if="loading" class="loader">
      <div class="spinner"></div>
      <p>Preparing your token...</p>
    </div>

    <!-- Content -->
    <div v-else class="content">

      <!-- Token -->
      <div class="order-wrapper">
        <CustomerOrderStatus
          v-if="hasToken"
          :orderData="finalOrderData"
          :tokenData="token"
          :qr="qrData"
          :kitchenQr="kitchenQrData"
          :invoiceUrl="resolvedInvoiceUrl"
        />
      </div>
      

      <!-- Feedback (same width as invoice) -->
      <div class="feedback-wrapper">
        <div class="feedback-card">

          <h2>✨ How was your visit?</h2>
          <p class="subtext">We’d love your quick feedback ❤️</p>

          <!-- Stars -->
          <div class="stars">
            <span 
              v-for="n in 5" 
              :key="n"
              @click="rating = n"
              :class="{ active: n <= rating }"
            >
              ★
            </span>
          </div>

          <!-- Comment -->
          <textarea 
            v-model="comment"
            placeholder="Tell us what you liked or didn’t..."
          ></textarea>

          <!-- Button -->
          <button @click="submitFeedback">
            Submit Feedback 🚀
          </button>

        </div>
      </div>

    </div>

  </div>
</template>

<script>
import CustomerOrderStatus from './CustomerOrderStatus.vue'
import axios from 'axios'

export default {
  props: ['uuid'],

  components: {
    CustomerOrderStatus
  },

  data() {
    return {
      loading: true,
      rating: 0,
      comment: '',
      submitted: false,

      // 🔥 actual data
      finalOrderData: null,
      token: null,
      qrData: null,
      kitchenQrData: null,
      invoiceLink: ''
    };
  },

  computed: {
    hasToken() {
      return !!this.token;
    },

    resolvedInvoiceUrl() {
      const invoiceNo = this.finalOrderData?.invoice_no

      return (
        this.finalOrderData?.meta?.invoice?.url ||
        this.invoiceLink ||
        (this.finalOrderData?.invoice_no
          ? `${window.location.origin}/billing/invoices/${encodeURIComponent(invoiceNo)}`
          : `${window.location.origin}/billing/invoices/${encodeURIComponent(this.uuid)}`)
      );
    }
  },

  async mounted() {
    try {
      const res = await axios.get(`/api/token/${this.uuid}`);
      console.log("TOken daa", res.data)
      this.finalOrderData = res.data.orderData;
      this.token = res.data.token;
      this.qrData = atob(res.data.qr || res.data?.qr || '') 
      this.kitchenQrData = atob(res.data.kitchenQr || res.data.data?.kitchenQr || '') 
      this.invoiceLink =
        res.data.orderData?.meta?.invoice?.url ||
        res.data.data?.orderData?.meta?.invoice?.url ||
        res.data.invoice_url ||
        res.data.invoiceUrl ||
        res.data.invoice?.url ||
        res.data.data?.invoice_url ||
        res.data.data?.invoiceUrl ||
        res.data.data?.invoice?.url ||
        ''
    } catch (e) {
      console.error('Error loading order:', e);
      alert('Something went wrong loading your order');
    } finally {
      this.loading = false;
    }
  },

  methods: {
    async submitFeedback() {
      if (this.submitted) return;

      if (!this.rating) {
        alert('Please select rating');
        return;
      }

      try {
        await axios.post('/api/feedback', {
          uuid: this.uuid,
          rating: this.rating,
          comment: this.comment.trim()
        });

        this.submitted = true;

        alert('Thanks for your feedback 🙌');

      } catch (e) {
        console.error(e);
        alert('Failed to submit feedback');
      }
    }
  }
};
</script>

<style scoped>

/* Page */
.page {
  background: #f5f5f5;
  min-height: 100vh;
}

/* Loader */
.loader {
  height: 100vh;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  background: #fff;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 5px solid #eee;
  border-top: 5px solid #000;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 10px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Content center */
.content {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding-top: 20px;
}

/* Invoice (receipt style) */
.invoice-frame {
  width: 320px;   /* 👈 same as receipt */
  height: 420px;
  border: none;
  background: white;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

/* Feedback wrapper */
.feedback-wrapper {
  margin-top: 40px; /* 👈 spacing from invoice */
  width: 100%;
  display: flex;
  justify-content: center;
}

/* Feedback card SAME WIDTH */
.feedback-card {
  width: 320px; /* 👈 MATCH invoice width */
  background: white;
  border-radius: 16px;
  padding: 20px;
  text-align: center;

  box-shadow: 0 10px 30px rgba(0,0,0,0.08);

  /* animation */
  opacity: 0;
  transform: translateY(30px);
  animation: slideFade 0.5s ease forwards;
}

@keyframes slideFade {
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Text */
h2 {
  font-size: 18px;
  margin-bottom: 5px;
}

.subtext {
  font-size: 13px;
  color: #777;
}

/* Stars */
.stars {
  font-size: 28px;
  margin: 15px 0;
}

.stars span {
  color: #ddd;
  cursor: pointer;
  transition: all 0.2s;
}

.stars span:hover {
  transform: scale(1.3);
}

.stars span.active {
  color: #ffb400;
}

/* Textarea */
textarea {
  width: 100%;
  margin-top: 10px;
  padding: 8px;
  border-radius: 8px;
  border: 1px solid #ddd;
  font-size: 13px;
}

/* Button */
button {
  margin-top: 12px;
  padding: 10px;
  width: 100%;
  background: black;
  color: white;
  border: none;
  border-radius: 20px;
  cursor: pointer;
}

button:hover {
  background: #333;
}

.order-wrapper {
  width: 100%;
  display: flex;
  justify-content: center;
  padding: 0 16px; /* 👈 important for mobile */
}

.order-wrapper > * {
  width: 100%;
  max-width: 420px; /* 👈 same as modern mobile cards */
}

</style>
