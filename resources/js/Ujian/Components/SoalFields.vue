<script setup>
import Field from './Field.vue';

/**
 * Isian soal (tipe, pertanyaan, opsi A–E, kunci, bobot) untuk form useForm.
 * Dipakai bank soal dan penyusunan soal ujian.
 */
const props = defineProps({ form: { type: Object, required: true } });
const huruf = ['A', 'B', 'C', 'D', 'E'];
</script>

<template>
    <div class="row">
        <div class="col-md-6">
            <Field label="Tipe soal" :error="form.errors.tipe" wajib>
                <div>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input id="tipe-pg" v-model="form.tipe" type="radio" value="pilihan_ganda" class="custom-control-input">
                        <label for="tipe-pg" class="custom-control-label">Pilihan ganda</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input id="tipe-essay" v-model="form.tipe" type="radio" value="essay" class="custom-control-input">
                        <label for="tipe-essay" class="custom-control-label">Essay</label>
                    </div>
                </div>
            </Field>
        </div>
        <div class="col-md-6">
            <Field label="Bobot" :error="form.errors.bobot" bantuan="Poin maksimal soal ini." wajib>
                <input v-model="form.bobot" type="number" step="0.01" min="0.01" class="form-control"
                       :class="{ 'is-invalid': form.errors.bobot }">
            </Field>
        </div>
    </div>

    <Field label="Pertanyaan" :error="form.errors.pertanyaan" wajib>
        <textarea v-model="form.pertanyaan" rows="4" class="form-control" :class="{ 'is-invalid': form.errors.pertanyaan }" />
    </Field>

    <template v-if="form.tipe === 'pilihan_ganda'">
        <label>Opsi jawaban <small class="text-muted">(A dan B wajib; pilih kunci di kiri)</small></label>
        <div v-for="h in huruf" :key="h" class="input-group mb-2">
            <div class="input-group-prepend">
                <div class="input-group-text">
                    <input v-model="form.kunci_jawaban" type="radio" :value="h" :aria-label="`Kunci ${h}`" class="mr-2">
                    <strong>{{ h }}</strong>
                </div>
            </div>
            <input v-model="form[`opsi_${h.toLowerCase()}`]" type="text" class="form-control"
                   :class="{ 'is-invalid': form.errors[`opsi_${h.toLowerCase()}`] }"
                   :placeholder="h === 'A' || h === 'B' ? 'Wajib' : 'Opsional'">
        </div>
        <div v-for="h in huruf" :key="`e-${h}`">
            <div v-if="form.errors[`opsi_${h.toLowerCase()}`]" class="invalid-feedback d-block">
                Opsi {{ h }}: {{ form.errors[`opsi_${h.toLowerCase()}`] }}
            </div>
        </div>
        <div v-if="form.errors.kunci_jawaban" class="invalid-feedback d-block">{{ form.errors.kunci_jawaban }}</div>
    </template>
</template>
