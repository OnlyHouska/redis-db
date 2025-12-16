import axios from 'axios';

/**
 * Set authentication token for all axios requests
 */
export const setAuthToken = (token: string | null) => {
    if (token) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        localStorage.setItem('token', token);
    } else {
        delete axios.defaults.headers.common['Authorization'];
        localStorage.removeItem('token');
    }
};

/**
 * Get token from localStorage
 */
export const getAuthToken = (): string | null => {
    return localStorage.getItem('token');
};
