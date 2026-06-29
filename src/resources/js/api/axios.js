import axios from 'axios';

const api = axios.create({
    baseURL: '/api',
    headers: {
        Accept: 'application/json',
    },
});

api.interceptors.response.use(
    response => response,
    error => Promise.reject(error)
);

export default api;