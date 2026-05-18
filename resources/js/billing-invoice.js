import './bootstrap';
import '../css/billing.css';
import { createApp } from 'vue';
import Invoices from './components/billing/Invoices.vue';

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

if (csrfToken && window.axios) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
}

const el = document.getElementById('billing-invoice-app');

if (el) {
    createApp(Invoices, {
        uuid: el.dataset.uuid,
    }).mount(el);
}
