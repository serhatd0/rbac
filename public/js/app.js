const API_URL = 'http://localhost:8080';

// Helper to get tokens
const getTokens = () => {
    return {
        accessToken: localStorage.getItem('accessToken'),
        refreshToken: localStorage.getItem('refreshToken')
    };
};

// Helper to set tokens
const setTokens = (access, refresh) => {
    localStorage.setItem('accessToken', access);
    localStorage.setItem('refreshToken', refresh);
};

// Helper to remove tokens
const removeTokens = () => {
    localStorage.removeItem('accessToken');
    localStorage.removeItem('refreshToken');
};

// Generic API Call
const apiCall = async (endpoint, method = 'GET', body = null, requireAuth = false) => {
    const headers = {
        'Content-Type': 'application/json'
    };

    if (requireAuth) {
        const { accessToken } = getTokens();
        if (accessToken) {
            headers['Authorization'] = `Bearer ${accessToken}`;
        }
    }

    const config = {
        method,
        headers,
        body: body ? JSON.stringify(body) : null
    };

    let response = await fetch(`${API_URL}${endpoint}`, config);

    // Initial 401 - Try Refresh
    if (response.status === 401 && requireAuth) {
        console.log('Access Token expired, trying refresh...');
        const { refreshToken } = getTokens();
        
        if (!refreshToken) {
            window.location.href = '/login';
            return null;
        }

        // Try to get new token
        const refreshResponse = await fetch(`${API_URL}/auth/refresh`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ refreshToken })
        });

        if (refreshResponse.ok) {
            const data = await refreshResponse.json();
            setTokens(data.accessToken, data.refreshToken);
            
            // Retry original request with new token
            headers['Authorization'] = `Bearer ${data.accessToken}`;
            response = await fetch(`${API_URL}${endpoint}`, {
                method,
                headers,
                body: body ? JSON.stringify(body) : null
            });
        } else {
            // Refresh failed - Logout
            removeTokens();
            window.location.href = '/login';
            return null;
        }
    }

    return response;
};

// UI Handling
const showError = (msg) => {
    const el = document.getElementById('error-msg');
    if (el) {
        el.innerText = msg;
        el.style.display = 'block';
    }
};

const showSuccess = (msg) => {
    const el = document.getElementById('success-msg');
    if (el) {
        el.innerText = msg;
        el.style.display = 'block';
    }
};
