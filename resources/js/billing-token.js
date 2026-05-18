import './bootstrap';
import './billing-echo';
import '../css/billing.css';
import { createApp } from 'vue';
import CustomerToken from './components/billing/CustomerToken.vue';

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

if (csrfToken && window.axios) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
}

const el = document.getElementById('billing-token-app');

if (el) {
    createApp(CustomerToken, {
        uuid: el.dataset.uuid,
    }).mount(el);
}
