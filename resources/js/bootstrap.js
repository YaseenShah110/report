/**
 * bootstrap.js - Application Bootstrap
 * -----------------------------------------------------------
 * Initializes Axios with CSRF token and default headers.
 * This file is loaded before the Vue app.
 */

import axios from "axios";

// Make Axios available globally
window.axios = axios;

// Set default headers for all AJAX requests
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

/**
 * CSRF Token Setup
 * Laravel requires a CSRF token for all POST/PUT/DELETE requests.
 * The token is stored in a meta tag in the HTML head.
 */
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common["X-CSRF-TOKEN"] = token.content;
} else {
    console.error("CSRF token not found. AJAX requests may fail.");
}
