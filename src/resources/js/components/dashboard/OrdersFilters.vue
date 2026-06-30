<template>
    <div class="bg-white border rounded-xl p-4 mb-4">
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">

            <div>
                <label class="text-sm sm:text-base">Pesquisar</label>

                <input
                    v-model="store.filters.search"
                    class="w-full border rounded px-3 py-2"
                    placeholder="ID do afiliado..."
                    aria-label="Pesquisar afiliado"
                />
            </div>
            <div>
                <label class="text-sm sm:text-base">Status</label>

                <select
                    v-model="store.filters.status"
                    class="w-full border rounded px-3 py-2"
                    aria-label="Pesquisar por status"
                >
                    <option :value="null">Todos</option>

                    <option
                        v-for="s in store.statuses"
                        :key="s.id"
                        :value="s.id"
                    >
                        {{ s.name }}
                    </option>
                </select>
            </div>
            <div>
                <label>Valor mínimo</label>
                <input
                    v-model="store.filters.min_value"
                    type="number"
                    class="w-full border rounded px-3 py-2"
                    aria-label="Filtrar por valor mínimo"
                />
            </div>

            <div>
                <label>Valor máximo</label>
                <input
                    v-model="store.filters.max_value"
                    type="number"
                    class="w-full border rounded px-3 py-2"
                    aria-label="Filtrar por valor máximo"
                />
            </div>
            <div>
                <label>Data início</label>
                <input
                    v-model="store.filters.date_from"
                    type="date"
                    class="w-full border rounded px-3 py-2"
                    aria-label="Filtrar por uma data inicial"
                />
            </div>
            <div>
                <label>Data fim</label>
                <input
                    v-model="store.filters.date_to"
                    type="date"
                    class="w-full border rounded px-3 py-2"
                    aria-label="Filtrar por uma data final"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
    import { ref, watch } from 'vue';
    import { useDebounceFn } from '@vueuse/core';
    import { useOrderStore } from '../../stores/orderStore';
    import { useRouter } from 'vue-router';

    const store = useOrderStore();
    const router = useRouter();

    const isInitialLoad = ref(true);

    // Função que empurra os estados atuais para a URL do navegador
    function syncUrl() {
        router.replace({
            query: {
                ...(store.filters.search ? { search: store.filters.search } : {}),
                ...(store.filters.status ? { status: store.filters.status } : {}),
                ...(store.filters.date_from ? { date_from: store.filters.date_from } : {}),
                ...(store.filters.date_to ? { date_to: store.filters.date_to } : {}),
                ...(store.filters.min_value ? { min_value: store.filters.min_value } : {}),
                ...(store.filters.max_value ? { max_value: store.filters.max_value } : {}),
            }
        });
    }

    // Evitar requisições excessivas enquanto digita
    const fetchDebounced = useDebounceFn(() => {
        store.meta.current_page = 1;
        syncUrl();
        store.fetchOrders();
    }, 400);

    // Monitora alterações nos filtros
    watch(
        () => store.filters,
        () => {
            if (isInitialLoad.value) {
                isInitialLoad.value = false;
                return;
            }
            fetchDebounced();
        },
        { deep: true }
    );
</script>