/**
 * Bootstrap JavaScript dependencies for the application.
 *
 * This file is intentionally left minimal and can be expanded if you need
 * libraries like Axios, Laravel Echo, or global helpers.
 */

import axios from 'axios';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
  window.axios.defaults.headers.common['X-CSRF-TOKEN'] = (token as HTMLMetaElement).content;
} else {
  console.warn('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}
