// Spring Boot API Integration Client
const API_BASE_URL = 'http://localhost:8080/api';

const apiClient = {
    async request(endpoint, method = 'GET', body = null) {
        const token = localStorage.getItem('jwt_token');
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        };
        
        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        const config = { method, headers };
        if (body) {
            config.body = JSON.stringify(body);
        }

        try {
            const response = await fetch(`${API_BASE_URL}${endpoint}`, config);
            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.error || 'API Error');
            }
            return data;
        } catch (error) {
            console.error('API Request Failed:', error);
            throw error;
        }
    },

    auth: {
        login: (mobile, password) => apiClient.request('/auth/login', 'POST', { mobile, password }),
        register: (userData) => apiClient.request('/auth/register', 'POST', userData)
    },

    users: {
        getProfile: () => apiClient.request('/users/me'),
        updateProfile: (data) => apiClient.request('/users/profile', 'PUT', data)
    },

    equipment: {
        getAll: () => apiClient.request('/equipment'),
        add: (data) => apiClient.request('/equipment', 'POST', data),
        getById: (id) => apiClient.request(`/equipment/${id}`)
    },

    bookings: {
        create: (equipmentId, data) => apiClient.request(`/bookings/equipment/${equipmentId}`, 'POST', data),
        getMyBookings: () => apiClient.request('/bookings/my-bookings')
    },
    
    dashboard: {
        getStats: () => apiClient.request('/dashboard/stats')
    }
};

// Export for frontend use
window.apiClient = apiClient;
