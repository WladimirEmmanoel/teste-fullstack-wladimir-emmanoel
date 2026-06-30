<template>
    <Transition name="fade">
        <div v-if="show" class="fixed inset-0 z-50">

            <div class="absolute inset-0 bg-black/40"
                @click="$emit('close')"
            />

            <aside ref="drawer"
                class="absolute right-0 top-0 h-full w-full sm:w-[520px] bg-white shadow-xl overflow-y-auto focus:outline-none"
                tabindex="-1"
                @keydown.esc="$emit('close')"
            >

                <div class="sticky top-0 bg-white border-b px-6 py-4 flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-bold">
                            Pedido #{{ order?.id }}
                        </h2>

                        <p class="text-sm text-gray-500">
                            {{ order?.affiliate?.id }} - {{ order?.affiliate?.email }}
                        </p>
                    </div>

                    <button
                        class="text-2xl hover:text-red-500 focus:outline-none focus:ring-2 focus:ring-red-400 rounded"
                        @click="$emit('close')"
                        aria-label="Fechar painel do pedido"
                    >
                        ×
                    </button>
                </div>

                <div class="p-6">
                    <div v-if="loading" class="space-y-3">
                        <div
                            v-for="i in 6"
                            :key="i"
                            class="h-4 bg-gray-200 animate-pulse rounded"
                        />
                    </div>

                    <div v-else-if="order">

                        <section class="mb-8">
                            <h3 class="font-bold mb-2">Informações do pedido</h3>

                            <p><strong>ID:</strong> {{ order.id }}</p>
                            <p><strong>Total:</strong> R$ {{ order.total_value }}</p>
                            <p><strong>Status:</strong> {{ order.status_name }}</p>
                        </section>

                        <section v-if="availableStatuses.length" class="mb-8">
                            <h3 class="font-bold mb-3">Alterar status</h3>

                            <div class="flex gap-2">
                                <select
                                    v-model="selectedStatus"
                                    class="border p-2 flex-1 focus:outline-none focus:ring-2 focus:ring-blue-400"
                                    aria-label="Selecionar novo status do pedido"
                                >
                                    <option
                                        v-for="status in availableStatuses"
                                        :key="status.value"
                                        :value="status.value"
                                    >
                                        {{ status.label }}
                                    </option>
                                </select>

                                <button
                                    @click="changeStatus"
                                    :disabled="saving"
                                    class="bg-blue-600 text-white px-3 rounded focus:outline-none focus:ring-2 focus:ring-blue-400 disabled:opacity-50"
                                    aria-label="Confirmar alteração de status"
                                >
                                    {{ saving ? 'Salvando...' : 'Alterar' }}
                                </button>
                            </div>

                            <div
                                v-if="error"
                                class="mt-3 text-sm text-red-600 bg-red-50 border border-red-200 p-3 rounded"
                                role="alert"
                                aria-live="polite"
                            >
                                {{ error }}
                            </div>
                        </section>

                        <section class="mb-8">
                            <h3 class="font-bold mb-3">Itens</h3>

                            <div
                                v-for="item in order.items"
                                :key="item.id"
                                class="border p-3 rounded mb-2"
                            >
                                <p>{{ item.product.title }}</p>
                                <p class="text-sm text-gray-500">
                                    {{ item.quantity }}x - R$ {{ item.price }}
                                </p>
                            </div>
                        </section>

                        <section>
                            <h3 class="font-bold mb-3">Histórico</h3>

                            <div
                                v-for="log in order.status_logs"
                                :key="log.id"
                                class="relative pl-6 mb-4 border-l-2 border-blue-500"
                            >
                                <div class="absolute -left-1 top-1 w-2 h-2 bg-blue-500 rounded-full"></div>

                                <p class="text-sm font-medium">
                                    {{ log.old_status }} → {{ log.new_status }}
                                </p>

                                <p class="text-xs text-gray-400">
                                    {{ new Date(log.created_at).toLocaleString('pt-BR') }}
                                </p>
                            </div>
                        </section>

                    </div>
                </div>
            </aside>
        </div>
    </Transition>
</template>

<script setup>
    import { ref, computed, watch, nextTick } from 'vue'
    import api from '../../api/axios'

    const props = defineProps({
        show: Boolean,
        loading: Boolean,
        order: Object
    })

    const emit = defineEmits(['close', 'updated'])

    const saving = ref(false)
    const error = ref('')
    const selectedStatus = ref(null)
    const drawer = ref(null)

    // Mapeia as transições de status permitidas para cada estado do pedido.
    const transitions = {
        1: [
            { value: 2, label: 'Approve' },
            { value: 3, label: 'Cancel' },
        ],
        2: [
            { value: 4, label: 'Refunded' },
        ],
        3: [
            { value: 4, label: 'Refunded' },
        ],
    }

    // Retorna apenas os status disponíveis para o estado atual do pedido.
    const availableStatuses = computed(() => {
        return transitions[props.order?.status] ?? []
    })

    // Reseta o estado do formulário e move o foco para o drawer ao carregar um novo pedido.
    watch(
        () => props.order,
        async () => {
            selectedStatus.value = null
            error.value = ''

            await nextTick()

            if (drawer.value) {
                drawer.value.focus()
            }
        }
    )

    // Altera o status do pedido e exibe a mensagem retornada pelo backend em caso de erro.
    async function changeStatus() {
        error.value = ''
        saving.value = true

        try {
            await api.post(`/orders/${props.order.id}/status`, {
                status: selectedStatus.value
            })

            emit('updated')
        } catch (e) {
            error.value =
                e?.response?.data?.message ||
                e?.response?.data?.error ||
                'Não foi possível alterar o status do pedido.'
        } finally {
            saving.value = false
        }
    }
</script>