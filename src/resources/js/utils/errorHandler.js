/**
 * Padroniza erros retornados pela API.
 */
export function setStoreError(store, error) {
    const apiError = handleApiError(error);

    store.error = apiError.message;
    store.validationErrors = apiError.errors;

    return apiError;
}

/**
 * Aplica o erro padronizado na Store.
 */
export function handleApiError(error) {
    // Erro retornado pelo backend
    if (error.response) {
        return {
            message: error.response.data.message ?? 'Ocorreu um erro.',
            errors: error.response.data.errors ?? {},
            status: error.response.status,
        };
    }

    // Sem resposta do servidor
    if (error.request) {
        return {
            message: 'Não foi possível conectar ao servidor.',
            errors: {},
            status: null,
        };
    }

    return {
        message: error.message ?? 'Erro inesperado.',
        errors: {},
        status: null,
    };
}