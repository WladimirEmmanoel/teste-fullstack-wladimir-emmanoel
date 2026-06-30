<template>
    <div class="bg-white border rounded-xl p-4">

        <div class="flex justify-between mb-4">
            <h2 class="text-lg font-semibold">Pedidos</h2>

            <div class="flex gap-2 items-center">
                <div v-if="store.selected.length" class="relative group">
                    <button class="bg-blue-500 text-white px-3 py-1 rounded text-sm sm:text-base" aria-label="Abrir ações em lote dos pedido">
                        Ações em lote
                    </button>

                    <div class="absolute hidden group-hover:block bg-white border shadow rounded p-2 z-10">

                        <button
                            class="block w-full text-left px-2 py-1 hover:bg-gray-100"
                            @click="store.updateMultipleStatus(2)"
                        >
                            Approve
                        </button>

                        <button
                            class="block w-full text-left px-2 py-1 hover:bg-gray-100"
                            @click="store.updateMultipleStatus(3)"
                        >
                            Cancel
                        </button>

                        <button
                            class="block w-full text-left px-2 py-1 hover:bg-gray-100"
                            @click="store.updateMultipleStatus(4)"
                        >
                            Refunded
                        </button>

                    </div>
                </div>
            </div>
        </div>
        <div v-if="store.loading" class="space-y-2">
            <div v-for="n in 5" :key="n" class="h-10 bg-gray-100 animate-pulse rounded" />
        </div>
        <div v-else-if="store.orders.length === 0"
             class="text-center text-gray-500 py-6">
            Nenhum pedido encontrado para os filtros aplicados.
        </div>

        <!-- Table Orders -->
        <table v-else class="w-full text-sm">
            <thead>
                <tr class="border-b text-left text-gray-500">

                    <th class="py-2">
                        <input
                            type="checkbox"
                            :checked="store.selected.length === store.orders.length"
                            @change="store.toggleSelectAll"
                            aria-label="Selecionar todos pedidos"
                        />
                    </th>

                    <th @click="store.toggleSort('id')" class="cursor-pointer">ID</th>

                    <th @click="store.toggleSort('affiliate_id')" class="cursor-pointer">ID Afiliado</th>

                    <th @click="store.toggleSort('total_value')" class="cursor-pointer">Valor pedido</th>

                    <th @click="store.toggleSort('status')" class="cursor-pointer">Status</th>

                    <th @click="store.toggleSort('created_at')" class="cursor-pointer">Data</th>

                    <th>Ações</th>
                </tr>
            </thead>

            <tbody>
                <tr v-for="order in store.orders"
                    :key="order.id"
                    class="border-b hover:bg-gray-50">

                    <td>
                        <input type="checkbox"
                            :checked="store.selected.includes(order.id)"
                            @change="store.toggleSelect(order.id)"
                            aria-label="Selecionar pedido"
                        />
                    </td>

                    <td>{{ order.id }}</td>

                    <td>{{ order.affiliate?.id ?? '-' }}</td>

                    <td>R$ {{ order.total_value }}</td>

                    <td>{{ store.statuses[order.status]?.['name'] ?? order.status }}</td>

                    <td>{{ formatDate(order.created_at) }}</td>

                    <td>
                        <div class="relative group">
                            <button class="text-blue-500 hover:underline" aria-label="Abrir ações do pedido">
                                Ações
                            </button>

                            <div class="absolute hidden group-hover:block bg-white border shadow rounded p-2 z-10">
                                <button v-for="action in availableActions(order.status)"
                                    :key="action.value"
                                    class="block text-sm px-2 py-1 hover:bg-gray-100 w-full text-left"
                                    @click="updateStatus(order.id, action.value)"
                                >
                                    {{ action.label }}
                                </button>

                                <div v-if="availableActions(order.status).length === 0"
                                     class="text-xs text-gray-400 px-2">
                                    Sem ações
                                </div>
                            </div>
                        </div>
                    </td>

                </tr>
            </tbody>
        </table>

        <div class="flex justify-between items-center mt-4 text-sm">
            
            <div class="text-gray-600">
                Total de registros: {{ store.meta.total }}
            </div>

            <div class="flex gap-2">
                <button
                    :disabled="store.meta.current_page === 1"
                    @click="() => { store.meta.current_page--; store.fetchOrders(); }"
                >
                    Anterior
                </button>

                <span>
                    {{ store.meta.current_page }} / {{ store.meta.last_page }}
                </span>

                <button
                    :disabled="store.meta.current_page === store.meta.last_page"
                    @click="() => { store.meta.current_page++; store.fetchOrders(); }"
                >
                    Próxima
                </button>
            </div>

        </div>

    </div>
</template>

<script setup>
    import { useOrderStore } from '../../stores/orderStore';
    import api from '../../api/axios';

    const store = useOrderStore();

    function availableActions(status) {
        if (status === 1) {
            return [
                { label: 'Approve', value: 2 },
                { label: 'Cancel', value: 3 },
            ];
        }

        if (status === 2 || status === 3) {
            return [
                { label: 'Refunded', value: 4 },
            ];
        }

        return [];
    }

    async function updateStatus(orderId, status) {
        await api.post(`/orders/${orderId}/status`, {
            status,
        });

        await store.fetchOrders();
    }

    function formatDate(date) {
        return new Date(date).toLocaleString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    }
</script>