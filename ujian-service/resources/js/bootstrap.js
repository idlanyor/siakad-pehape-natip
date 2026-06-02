import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// JWT Token Management
const getToken = () => {
    return localStorage.getItem('jwt_token');
};

const setToken = (token) => {
    localStorage.setItem('jwt_token', token);
};

const removeToken = () => {
    localStorage.removeItem('jwt_token');
};

// Axios Request Interceptor - Add JWT token to all requests
window.axios.interceptors.request.use(
    (config) => {
        const token = getToken();
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Axios Response Interceptor - Enhanced error handling
window.axios.interceptors.response.use(
    (response) => {
        return response;
    },
    async (error) => {
        const originalRequest = error.config;

        // Handle network errors
        if (!error.response) {
            error.isNetworkError = true;
            error.userMessage = 'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.';
            return Promise.reject(error);
        }

        const status = error.response.status;

        // Handle 401 Unauthorized - Token expired
        if (status === 401 && !originalRequest._retry) {
            removeToken();
            
            // Redirect to SIAKAD ujian login
            const siakadUrl = import.meta.env.VITE_SIAKAD_URL || 'http://localhost:8000';
            window.location.href = siakadUrl;
            return Promise.reject(error);
        }

        // Handle 403 Forbidden
        if (status === 403) {
            error.userMessage = 'Anda tidak memiliki izin untuk mengakses resource ini.';
        }

        // Handle 404 Not Found
        if (status === 404) {
            error.userMessage = 'Resource yang diminta tidak ditemukan.';
        }

        // Handle 422 Validation Error
        if (status === 422) {
            error.userMessage = 'Data yang Anda masukkan tidak valid.';
            error.validationErrors = error.response.data.errors || {};
        }

        // Handle 429 Too Many Requests
        if (status === 429) {
            error.userMessage = 'Terlalu banyak permintaan. Silakan coba lagi nanti.';
        }

        // Handle 500 Server Error
        if (status >= 500) {
            error.userMessage = 'Terjadi kesalahan pada server. Tim kami telah diberitahu.';
            
            // Log to console in development
            if (import.meta.env.DEV) {
                console.error('Server Error:', error.response.data);
            }
        }

        // Handle timeout
        if (error.code === 'ECONNABORTED') {
            error.userMessage = 'Permintaan memakan waktu terlalu lama. Silakan coba lagi.';
        }

        // Retry logic for network errors and 5xx errors (max 3 retries)
        if (!originalRequest._retry && (error.isNetworkError || status >= 500)) {
            originalRequest._retryCount = originalRequest._retryCount || 0;
            
            if (originalRequest._retryCount < 3) {
                originalRequest._retryCount++;
                originalRequest._retry = true;

                // Exponential backoff: 1s, 2s, 4s
                const delay = 1000 * Math.pow(2, originalRequest._retryCount - 1);
                
                await new Promise(resolve => setTimeout(resolve, delay));
                
                return window.axios(originalRequest);
            }
        }

        return Promise.reject(error);
    }
);

// Export token management functions
window.tokenManager = {
    get: getToken,
    set: setToken,
    remove: removeToken,
};

// Laravel Echo Configuration for Reverb
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    authorizer: (channel, options) => {
        return {
            authorize: (socketId, callback) => {
                axios.post('/api/broadcasting/auth', {
                    socket_id: socketId,
                    channel_name: channel.name
                })
                .then(response => {
                    callback(null, response.data);
                })
                .catch(error => {
                    callback(error);
                });
            }
        };
    },
});
