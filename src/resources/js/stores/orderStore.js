import api from '../api/axios';
import { defineStore } from 'pinia';
import { setStoreError } from '../utils/errorHandler';

export const useOrderStore = defineStore('orders', {
    state: () => ({
        metrics: {
            total_orders: 0,
            total_revenue: 0,
            avg_ticket: 0,
            cancel_rate: 0,
        },

        orders: [],

        error: null,
        loading: false,
        validationErrors: {},

        cachedTime: null,
        cacheMinutes: null,
    }),

    actions: {
        async fetchMetrics() {
            this.loading = true;
            this.error = null;
            this.validationErrors = {};

            try {

                const res = await api.get('/orders/metrics');

                this.metrics = res.data.data;

                const cachedTime = res.data.meta.cached_time;
                this.cachedTime = new Date(cachedTime);
                
                this.cacheMinutes = this.calcCacheMinutes(this.cachedTime);

            } catch (error) {
                setStoreError(this, error);
            } finally {
                this.loading = false;
            }
        },

        async fetchOrders() {
            this.loading = true;
            this.error = null;
            this.validationErrors = {};

            try {
                const res = await api.get('/orders');

                this.orders = res.data.data;

            } catch (error) {
                setStoreError(this, error);
            } finally {
                this.loading = false;
            }
        },

        // calcula o tempo do cache em minutos
        calcCacheMinutes(date) {
            return Math.floor(
                (Date.now() - date.getTime()) / 60000
            );
        }
    }
});