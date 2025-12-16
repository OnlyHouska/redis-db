// resources/js/axios-config.ts
import axios from 'axios';

/**
 * Axios global configuration
 *
 * Sets up base URL, default headers, and request interceptor
 * to automatically include JWT token from localStorage.
 */

// Base configuration
axios.defaults.baseURL = 'http://localhost:3000';
axios.defaults.headers.common['Accept'] = 'application/json';
axios.defaults.headers.common['Content-Type'] = 'application/json';

// Request interceptor - automatically add token to every request
axios.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('token');
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Response interceptor - handle 401 errors
axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            // Token expired or invalid - redirect to login
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            window.location.href = '/?login=true';
        }
        return Promise.reject(error);
    }
);

export default axios;
