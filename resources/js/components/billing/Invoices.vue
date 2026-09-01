<template>
  <div class="page">

    <!-- Loader -->
    <div v-if="loading" class="loader">
      <div class="spinner"></div>
      <p>Preparing your invoice...</p>
    </div>

    <div v-else-if="requiresVerification && !verified" class="gate">
      <form class="gate-card" @submit.prevent="verifyPhone">
        <p class="gate-kicker">Invoice verification</p>
        <h1>Confirm registered phone</h1>
        <p class="gate-copy">This invoice is more than 24 hours old. Enter the customer phone number linked with this invoice to open it.</p>

        <label>
          Contact number
          <input
            v-model.trim="phone"
            type="tel"
            autocomplete="tel"
            placeholder="Registered phone number"
          />
        </label>

        <div v-if="errorMessage" class="error-box">
          {{ errorMessage }}
        </div>

        <button :disabled="verifying">
          {{ verifying ? 'Checking...' : 'Open invoice' }}
        </button>
      </form>
    </div>

    <!-- Content -->
    <div v-else class="content">

      <!-- Invoice -->
      <iframe
        :src="invoiceUrl"
        class="invoice-frame"
      ></iframe>

      <!-- Feedback -->
      <div class="feedback-wrapper">

        <div class="feedback-card">

          <!-- SUCCESS -->
          <div v-if="submitted" class="success-state">

            <div class="plane-wrapper">
              ✈️
            </div>

            <h2>Review Sent</h2>

            <p class="subtext">
              Thank you for sharing your feedback ❤️
            </p>

          </div>

          <!-- FORM -->
          <template v-else>

            <h2>✨ How was your visit?</h2>

            <p class="subtext">
              We’d love your quick feedback ❤️
            </p>

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

            <!-- ERROR -->
            <div v-if="errorMessage" class="error-box">
              {{ errorMessage }}
            </div>

            <!-- BUTTON -->
            <button
              @click="submitFeedback"
              :disabled="submitting"
            >

              <span v-if="submitting">
                Sending...
              </span>

              <span v-else>
                Submit Feedback 🚀
              </span>

            </button>

          </template>

        </div>

      </div>

    </div>

  </div>
</template>

<script>
export default {

  props: ['uuid', 'custinfo'],

  data() {

    return {

      loading: true,
      requiresVerification: false,
      verified: false,
      accessToken: '',
      phone: '',
      verifying: false,

      rating: 0,

      comment: '',

      submitting: false,

      submitted: false,

      errorMessage: '',

    };
  },

  computed: {

    invoiceUrl() {

      const url = new URL(`/api/invoice/${this.uuid}`, window.location.origin);

      if (this.custinfo) {

        url.searchParams.set('custinfo', '1');
      }

      if (this.accessToken) {

        url.searchParams.set('access_token', this.accessToken);
      }

      return url.toString();
    }
  },

  mounted() {
    this.loadAccessStatus();
  },

  methods: {
    async loadAccessStatus() {
      this.errorMessage = '';

      try {

        const response = await fetch(`/api/invoice/${encodeURIComponent(this.uuid)}/access-status`, {
          headers: {
            'Accept': 'application/json'
          }
        });

        const result = await response.json();

        if (!response.ok) {

          throw new Error(result.message || 'Invoice not found');
        }

        this.requiresVerification = Boolean(result.requires_verification);
        this.verified = !this.requiresVerification;

      } catch (error) {

        this.errorMessage = error.message || 'Unable to prepare invoice';
        this.requiresVerification = true;
        this.verified = false;

      } finally {

        this.loading = false;
      }
    },

    async verifyPhone() {
      this.errorMessage = '';

      if (!this.phone) {

        this.errorMessage = 'Please enter the registered phone number';

        return;
      }

      try {

        this.verifying = true;

        const response = await fetch(`/api/invoice/${encodeURIComponent(this.uuid)}/verify-access`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
          },
          body: JSON.stringify({
            phone: this.phone
          })
        });

        const result = await response.json();

        if (!response.ok) {

          throw new Error(result.message || 'Phone number could not be verified');
        }

        this.accessToken = result.access_token || '';
        this.verified = Boolean(this.accessToken);

      } catch (error) {

        this.errorMessage = error.message || 'Phone number could not be verified';

      } finally {

        this.verifying = false;
      }
    },

    async submitFeedback() {

      this.errorMessage = '';

      if (!this.rating) {

        this.errorMessage = 'Please select a rating';

        return;
      }

      try {

        this.submitting = true;

        const response = await fetch('/api/reviews', {

          method: 'POST',

          headers: {

            'Content-Type': 'application/json',

            'Accept': 'application/json',

            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
          },

          body: JSON.stringify({

            uuid: this.uuid,

            rating: this.rating,

            comment: this.comment
          })
        });

        const result = await response.json();

        if (!response.ok) {

          throw new Error(
            result.message || 'Failed to send feedback'
          );
        }

        this.submitted = true;

      } catch (error) {

        console.error(error);

        this.errorMessage = error.message || 'Something went wrong';

      } finally {

        this.submitting = false;
      }
    }
  }
};
</script>

<style scoped>

/* PAGE */
.page {
  background: #f5f5f5;
  min-height: 100vh;
}

/* LOADER */
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
  to {
    transform: rotate(360deg);
  }
}

/* CONTENT */
.content {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding-top: 20px;
  padding-bottom: 60px;
}

.gate {
  min-height: 100vh;
  display: grid;
  place-items: center;
  padding: 24px;
  background: #f8fafc;
}

.gate-card {
  width: min(100%, 420px);
  border: 1px solid #e2e8f0;
  border-radius: 18px;
  background: #fff;
  padding: 24px;
  box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
}

.gate-kicker {
  margin: 0 0 8px;
  font-size: 12px;
  font-weight: 900;
  letter-spacing: .12em;
  text-transform: uppercase;
  color: #2563eb;
}

.gate-card h1 {
  margin: 0;
  font-size: 24px;
  color: #0f172a;
}

.gate-copy {
  margin: 10px 0 20px;
  font-size: 14px;
  line-height: 1.6;
  color: #64748b;
}

.gate-card label {
  display: grid;
  gap: 8px;
  font-size: 14px;
  font-weight: 800;
  color: #334155;
}

.gate-card input {
  min-height: 46px;
  border: 1px solid #cbd5e1;
  border-radius: 12px;
  padding: 0 14px;
  font-size: 15px;
  outline: none;
}

.gate-card input:focus {
  border-color: #2563eb;
  box-shadow: 0 0 0 4px rgba(37, 99, 235, .12);
}

.gate-card button {
  margin-top: 16px;
}

/* INVOICE */
.invoice-frame {
  width: 320px;
  height: 420px;
  border: none;
  background: white;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

/* FEEDBACK */
.feedback-wrapper {
  margin-top: 40px;
  width: 100%;
  display: flex;
  justify-content: center;
}

.feedback-card {

  width: 320px;

  background: white;

  border-radius: 20px;

  padding: 22px;

  text-align: center;

  box-shadow: 0 10px 30px rgba(0,0,0,0.08);

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

/* TEXT */
h2 {
  font-size: 18px;
  margin-bottom: 5px;
}

.subtext {
  font-size: 13px;
  color: #777;
  line-height: 1.5;
}

/* STARS */
.stars {
  font-size: 30px;
  margin: 18px 0;
}

.stars span {
  color: #ddd;
  cursor: pointer;
  transition: all 0.2s;
}

.stars span:hover {
  transform: scale(1.25);
}

.stars span.active {
  color: #ffb400;
}

/* TEXTAREA */
textarea {

  width: 100%;

  min-height: 100px;

  margin-top: 10px;

  padding: 12px;

  border-radius: 12px;

  border: 1px solid #ddd;

  font-size: 13px;

  resize: none;

  outline: none;
}

textarea:focus {
  border-color: #111;
}

/* BUTTON */
button {

  margin-top: 14px;

  padding: 12px;

  width: 100%;

  background: black;

  color: white;

  border: none;

  border-radius: 999px;

  cursor: pointer;

  font-weight: 600;

  transition: all 0.2s;
}

button:hover {
  background: #222;
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* ERROR */
.error-box {

  margin-top: 12px;

  background: #fff1f2;

  color: #dc2626;

  border: 1px solid #fecdd3;

  padding: 10px;

  border-radius: 12px;

  font-size: 13px;
}

/* SUCCESS */
.success-state {
  padding: 20px 10px;
}

.plane-wrapper {

  font-size: 64px;

  margin-bottom: 20px;

  animation: flyPlane 1.2s ease forwards;
}

@keyframes flyPlane {

  0% {

    opacity: 0;

    transform: translateX(-80px) rotate(-20deg);
  }

  60% {

    opacity: 1;

    transform: translateX(10px) rotate(8deg);
  }

  100% {

    opacity: 1;

    transform: translateX(0) rotate(0deg);
  }
}

</style>
