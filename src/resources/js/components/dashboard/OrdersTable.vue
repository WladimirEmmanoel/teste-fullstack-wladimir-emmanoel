<template>
    <div class="bg-white border rounded-xl p-4">

        <div class="flex justify-between mb-4">
            <h2 class="text-lg font-semibold">Pedidos</h2>

            <div v-if="store.selected.length" class="relative group">
                <button class="bg-blue-500 text-white px-3 py-1 rounded text-sm">
                    Ações em lote
                </button>

                <div class="absolute hidden group-hover:block bg-white border shadow rounded p-2 z-10">
                    <button class="block w-full text-left px-2 py-1 hover:bg-gray-100"
                        @click="store.updateMultipleStatus(2)">
                        Approve
                    </button>

                    <button class="block w-full text-left px-2 py-1 hover:bg-gray-100"
                        @click="store.updateMultipleStatus(3)">
                        Cancel
                    </button>

                    <button class="block w-full text-left px-2 py-1 hover:bg-gray-100"
                        @click="store.updateMultipleStatus(4)">
                        Refunded
                    </button>
                </div>
            </div>
        </div>

        <div v-if="store.loading" class="space-y-2">
            <div v-for="n in 5" :key="n" class="h-10 bg-gray-100 animate-pulse rounded" />
        </div>

        <div v-else-if="store.orders.length === 0"
            class="text-center text-gray-500 py-6">
            Nenhum pedido encontrado.
        </div>

        <div v-else>
            <div class="hidden md:block">
                <table class="w-full text-sm border">
                    <thead>
                        <tr class="border-b text-left text-gray-500">
                            <th>
                                <input type="checkbox"
                                    :checked="store.selected.length === store.orders.length"
                                    @change="store.toggleSelectAll"
                                />
                            </th>

                            <th @click="store.toggleSort('id')" class="cursor-pointer">ID</th>
                            <th @click="store.toggleSort('total_value')" class="cursor-pointer">ID Afiliado</th>
                            <th @click="store.toggleSort('total_value')" class="cursor-pointer">Valor</th>
                            <th @click="store.toggleSort('total_value')" class="cursor-pointer">Status</th>
                            <th @click="store.toggleSort('total_value')" class="cursor-pointer">Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="order in store.orders"
                            :key="order.id"
                            class="border-b hover:bg-gray-50 cursor-pointer"
                            @click="openDrawer(order)">

                            <td>
                                <input type="checkbox"
                                    :checked="store.selected.includes(order.id)"
                                    @click.stop="store.toggleSelect(order.id)"
                                />
                            </td>

                            <td>{{ order.id }}</td>
                            <td>{{ order.affiliate?.id ?? '-' }}</td>
                            <td>R$ {{ order.total_value }}</td>
                            <td>{{ store.statuses[order.status]?.name ?? order.status }}</td>
                            <td>{{ formatDate(order.created_at) }}</td>

                            <td @click.stop>
                                <div class="relative group">
                                    <button class="text-blue-500">Ações</button>

                                    <div class="absolute hidden group-hover:block bg-white border shadow rounded p-2 z-10">
                                        <button
                                            v-for="action in availableActions(order.status)"
                                            :key="action.value"
                                            class="block w-full text-left px-2 py-1 hover:bg-gray-100"
                                            @click="updateStatus(order.id, action.value)"
                                        >
                                            {{ action.label }}
                                        </button>

                                        <div v-if="availableActions(order.status).length === 0"
                                            class="text-xs text-gray-400">
                                            Sem ações
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="grid gap-3 md:hidden">
                <div
                    v-for="order in store.orders"
                    :key="order.id"
                    class="border rounded-lg p-3 shadow-sm bg-white cursor-pointer active:scale-[0.99] transition"
                    @click="openDrawer(order)"
                >
                    <div class="flex justify-between items-center mb-2">
                        <div class="font-semibold">#{{ order.id }}</div>

                        <input
                            type="checkbox"
                            :checked="store.selected.includes(order.id)"
                            @click.stop="store.toggleSelect(order.id)"
                        />
                    </div>

                    <div class="text-sm text-gray-600 space-y-1">
                        <div><strong>Afiliado:</strong> {{ order.affiliate?.id ?? '-' }}</div>
                        <div><strong>Valor:</strong> R$ {{ order.total_value }}</div>
                        <div>
                            <strong>Status:</strong>
                            {{ store.statuses[order.status]?.name ?? order.status }}
                        </div>
                        <div>
                            <strong>Data:</strong>
                            {{ formatDate(order.created_at) }}
                        </div>
                    </div>

                    <div class="mt-3 flex gap-2 flex-wrap" @click.stop>
                        <button
                            v-for="action in availableActions(order.status)"
                            :key="action.value"
                            class="text-xs px-2 py-1 border rounded hover:bg-gray-100"
                            @click="updateStatus(order.id, action.value)"
                        >
                            {{ action.label }}
                        </button>

                        <span v-if="availableActions(order.status).length === 0"
                            class="text-xs text-gray-400">
                            Sem ações
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="flex justify-between mt-4 text-sm">
            <div>Total: {{ store.meta.total }}</div>

            <div class="flex gap-2">
                <button
                    :disabled="store.meta.current_page === 1"
                    @click="() => { store.meta.current_page--; store.fetchOrders(); }">
                    Anterior
                </button>

                <span>
                    {{ store.meta.current_page }} / {{ store.meta.last_page }}
                </span>

                <button
                    :disabled="store.meta.current_page === store.meta.last_page"
                    @click="() => { store.meta.current_page++; store.fetchOrders(); }">
                    Próxima
                </button>
            </div>
        </div>

        <OrderDrawer
            :show="drawerOpen"
            :loading="drawerLoading"
            :order="selectedOrder"
            @close="drawerOpen = false"
            @updated="refreshDrawer"
        />
    </div>
</template>

<script setup>
    import api from '../../api/axios'
    import { useOrderStore } from '../../stores/orderStore'
    import { ref } from 'vue'
    import OrderDrawer from '../drawer/OrderDrawer.vue'

    const store = useOrderStore()

    const drawerOpen = ref(false)
    const drawerLoading = ref(false)
    const selectedOrder = ref(null)


    // Retorna as ações disponíveis para o status atual do pedido.
    function availableActions(status) {
        const map = {
            1: [
                { label: 'Approve', value: 2 },
                { label: 'Cancel', value: 3 },
            ],
            2: [
                { label: 'Refunded', value: 4 },
            ],
            3: [
                { label: 'Refunded', value: 4 },
            ],
        }

        return map[status] ?? []
    }

    // Atualiza o status de um pedido e recarrega o orders.
    async function updateStatus(orderId, status) {
        await api.post(`/orders/${orderId}/status`, { status })
        await store.fetchOrders()
    }

    // Formata uma data para o padrão brasileiro.
    function formatDate(date) {
        return new Date(date).toLocaleString('pt-BR')
    }

    // Abre o drawer e carrega os detalhes completos do pedido.
    async function openDrawer(order) {
        drawerOpen.value = true
        drawerLoading.value = true
        selectedOrder.value = null

        try {
            const { data } = await api.get(`/orders/${order.id}`)
            selectedOrder.value = data.data
        } finally {
            drawerLoading.value = false
        }
    }

    // Atualiza os dados do drawer e da tabela após alterações no pedido.
    async function refreshDrawer() {
        if (!selectedOrder.value) return

        await openDrawer(selectedOrder.value)
        await store.fetchOrders()
    }
</script>