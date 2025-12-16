// resources/js/utils/auth.ts
/**
 * Set authentication token
 * Token is stored in localStorage and automatically included
 * in requests via axios interceptor.
 */
export const setAuthToken = (token: string | null) => {
    if (token) {
        localStorage.setItem('token', token);
    } else {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
    }
};

/**
 * Get token from localStorage
 */
export const getAuthToken = (): string | null => {
    return localStorage.getItem('token');
};
