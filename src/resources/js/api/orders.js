import api from './axios';

export function getOrders(params = {}) {
    const response = await api.get('/orders', { params });
    return response.data.data;
}

export function getMetrics() {
    const response = await api.get('/orders/metrics');
    return response.data.data;
}

export function getOrder(id) {
    const response = await api.get(`/orders/${id}`);
    return response.data.data;
}

export function updateStatus(id, status) {
    const response = await api.patch(`/orders/${id}/status`, {
        status,
    });

    return response.data.data;
}post