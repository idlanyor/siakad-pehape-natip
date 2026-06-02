import { reactive, computed } from 'vue';
import { router } from '@inertiajs/vue3';

export function useForm(initialData = {}) {
    const form = reactive({
        data: { ...initialData },
        errors: {},
        processing: false,
        wasSuccessful: false,
        recentlySuccessful: false
    });

    const hasErrors = computed(() => {
        return Object.keys(form.errors).length > 0;
    });

    const reset = (...fields) => {
        if (fields.length === 0) {
            form.data = { ...initialData };
            form.errors = {};
        } else {
            fields.forEach(field => {
                form.data[field] = initialData[field];
                delete form.errors[field];
            });
        }
    };

    const clearErrors = (...fields) => {
        if (fields.length === 0) {
            form.errors = {};
        } else {
            fields.forEach(field => {
                delete form.errors[field];
            });
        }
    };

    const setError = (field, message) => {
        form.errors[field] = message;
    };

    const setErrors = (errors) => {
        form.errors = { ...errors };
    };

    const submit = (method, url, options = {}) => {
        form.processing = true;
        form.wasSuccessful = false;
        form.recentlySuccessful = false;

        const submitOptions = {
            ...options,
            data: form.data,
            onSuccess: (page) => {
                form.wasSuccessful = true;
                form.recentlySuccessful = true;
                form.errors = {};
                
                setTimeout(() => {
                    form.recentlySuccessful = false;
                }, 2000);

                if (options.onSuccess) {
                    options.onSuccess(page);
                }
            },
            onError: (errors) => {
                form.errors = errors;
                form.wasSuccessful = false;

                if (options.onError) {
                    options.onError(errors);
                }
            },
            onFinish: () => {
                form.processing = false;

                if (options.onFinish) {
                    options.onFinish();
                }
            }
        };

        router[method](url, submitOptions);
    };

    const get = (url, options = {}) => {
        submit('get', url, options);
    };

    const post = (url, options = {}) => {
        submit('post', url, options);
    };

    const put = (url, options = {}) => {
        submit('put', url, options);
    };

    const patch = (url, options = {}) => {
        submit('patch', url, options);
    };

    const del = (url, options = {}) => {
        submit('delete', url, options);
    };

    return {
        ...form,
        hasErrors,
        reset,
        clearErrors,
        setError,
        setErrors,
        submit,
        get,
        post,
        put,
        patch,
        delete: del
    };
}
