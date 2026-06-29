<template>
  <div class="p-6 space-y-6">
    <h1 class="text-3xl font-bold">Dashboard</h1>

    <!-- Card Métricas -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 space-y-6 mb-6">
        
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">
                Métricas de Pedidos
            </h1>

            <span class="text-xs sm:text-sm text-gray-500">
                Atualizado há {{ store.cacheMinutes ?? 0 }} min
            </span>
        </div>

        <div v-if="store.loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div 
                v-for="n in 4" 
                :key="n"
                class="p-4 rounded-lg border border-gray-100 animate-pulse space-y-3"
            >
                <div class="h-3 bg-gray-200 w-1/2 rounded"></div>
                <div class="h-6 bg-gray-300 w-3/4 rounded"></div>
            </div>
        </div>
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <MetricsCard 
                title="Total de ordens"
                :value="metrics.total_orders"
                :loading="store.loading"
            />

            <MetricsCard 
                title="Receita total"
                :value="metrics.total_revenue"
                :loading="store.loading"
            />

            <MetricsCard 
                title="Ticket médio"
                :value="metrics.avg_ticket"
                :loading="store.loading"
            />

            <MetricsCard 
                title="Cancelamento"
                :value="metrics.cancel_rate + '%'"
                :loading="store.loading"
            />
        </div>
    </div>

    <!-- Tabela de pedidos -->
    <OrdersTable />
  </div>
</template>

<script setup>
    import { onMounted, onUnmounted} from 'vue';
    import { storeToRefs } from 'pinia';
    import { useOrderStore } from '../stores/orderStore';

    import MetricsCard from '../components/dashboard/MetricsCard.vue';
    import OrdersTable from '../components/dashboard/OrdersTable.vue';

    const store = useOrderStore();

    let interval = null;

    const { 
        metrics,
        orders,
        loading,
        error,
    } = storeToRefs(store);

    /*
        Atualiza as metricas a cada 1 minuto.
    */
    onMounted(() => {
        store.fetchMetrics();

        interval = setInterval(() => {
            store.fetchMetrics();
        }, 60000);
    });

    onUnmounted(() => {
        clearInterval(interval)
    });
</script>