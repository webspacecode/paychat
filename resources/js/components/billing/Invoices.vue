<template>
  <div class="page">

    <!-- Loader -->
    <div v-if="loading" class="loader">
      <div class="spinner"></div>
      <p>Preparing your invoice...</p>
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

  props: ['uuid'],

  data() {

    return {

      loading: true,

      rating: 0,

      comment: '',

      submitting: false,

      submitted: false,

      errorMessage: '',

    };
  },

  computed: {

    invoiceUrl() {

      return `${window.location.origin}/api/invoice/${this.uuid}`;
    }
  },

  mounted() {

    setTimeout(() => {

      this.loading = false;

    }, 800);
  },

  methods: {

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
