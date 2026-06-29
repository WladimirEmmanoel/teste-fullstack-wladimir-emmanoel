import http from './http';

export function getOrders(params = {}) {
    return http.get('/orders', { params });
}

export function getMetrics() {
    return http.get('/orders/metrics');
}

export function getOrder(id) {
    return http.get(`/orders/${id}`);
}

export function updateStatus(id, status) {
    return http.post(`/orders/${id}/status`, { status });
}post