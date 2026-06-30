import api from '../api/axios';
import { defineStore } from 'pinia';
import { setStoreError } from '../utils/errorHandler';

export const useOrderStore = defineStore('orders', {
    state: () => ({
        // Global
        error: null,
        loading: false,
        validationErrors: {},

        // Metrics Orders
        metrics: {
            total_orders: 0,
            total_revenue: 0,
            avg_ticket: 0,
            cancel_rate: 0,
        },
        cachedTime: null,
        cacheMinutes: null,

        // Datatable Orders
        statuses: [],
        orders: [],
        meta: {
            current_page: 1,
            last_page: 1,
            per_page: 10,
            total: 0,
        },
        filters: {
            search: '',
            status: null,
            date_from: null,
            date_to: null,
            min_value: null,
            max_value: null,
        },
        sort: {
            by: 'created_at',
            dir: 'desc',
        },
        selected: [],
    }),

    actions: {
        // Card de métricas
        async fetchMetrics() {
            this.loading = true;
            this.error = null;
            this.validationErrors = {};

            try {

                const res = await api.get('/orders/metrics');

                this.metrics = res.data.data;

                const cachedTime = res.data.meta.cached_time;

                if (cachedTime) {
                    this.cachedTime = new Date(cachedTime);
                    this.cacheMinutes = this.calcCacheMinutes(this.cachedTime);
                }

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
        },

        async fetchOrders() {
            this.loading = true;
            try {
                const res = await api.get('/orders', {
                    params: {
                        search: this.filters.search,
                        status: this.filters.status,

                        date_from: this.filters.date_from,
                        date_to: this.filters.date_to,

                        min_value: this.filters.min_value,
                        max_value: this.filters.max_value,

                        sort_by: this.sort.by,
                        sort_dir: this.sort.dir,
                        page: this.meta.current_page,
                        per_page: this.meta.per_page,
                    }
                });

                this.orders = res.data.data;
                this.meta = res.data.meta;
                this.selected = [];
            } catch (error) {
                setStoreError(this, error);
            } finally {
                this.loading = false;
            }
        },

        async fetchStatuses() {
            try {
                const res = await api.get('/orders/statuses');
                this.statuses = res.data.data;
            } catch (error) {
                setStoreError(this, error);
            }
        },

        toggleSort(field) {
            if (this.sort.by === field) {
                this.sort.dir =
                    this.sort.dir === 'asc' ? 'desc' : 'asc';
            } else {
                this.sort.by = field;
                this.sort.dir = 'asc';
            }

            this.meta.current_page = 1;
            this.fetchOrders();
        },

        toggleSelect(id) {
            if (this.selected.includes(id)) {
                this.selected = this.selected.filter(i => i !== id);
            } else {
                this.selected.push(id);
            }
        },

        toggleSelectAll() {
            if (this.selected.length === this.orders.length) {
                this.selected = [];
            } else {
                this.selected = this.orders.map(o => o.id);
            }
        },

        async updateMultipleStatus(status) {
            try {
                await api.post('/orders/update-multiple-status', {
                    ids: this.selected,
                    status,
                });

                this.selected = [];
                await this.fetchOrders();
            } catch (e) {
                setStoreError(this, e);
            }
        },
    }
});