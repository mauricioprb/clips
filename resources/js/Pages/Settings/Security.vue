<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Password from 'primevue/password';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import SettingsLayout from '@/Components/Layout/SettingsLayout.vue';
import FormField from '@/Components/UI/FormField.vue';
import SettingsSection from '@/Components/UI/SettingsSection.vue';
import { useI18n } from '@/Composables/useI18n';

defineOptions({ layout: [AppLayout, SettingsLayout] });

const { t } = useI18n();
const passwordForm = useForm({ current_password: '', password: '', password_confirmation: '' });
const deleteForm = useForm({ password: '' });
const confirmingDeletion = ref(false);

function updatePassword(): void {
    passwordForm.put('/configuracoes/senha', {
        preserveScroll: true,
        onFinish: () => passwordForm.reset(),
    });
}

function deleteAccount(): void {
    deleteForm.delete('/configuracoes/conta', { onFinish: () => deleteForm.reset() });
}
</script>

<template>
    <Head :title="t('settings.tab.security')" />

    <div class="grid grid-cols-1 gap-6">
        <form class="card" @submit.prevent="updatePassword">
            <SettingsSection :title="t('security.passwordTitle')" :description="t('security.passwordDescription')">
                <FormField
                    id="current-password"
                    v-slot="{ id, invalid }"
                    :label="t('security.current')"
                    :error="passwordForm.errors.current_password"
                >
                    <Password
                        v-model="passwordForm.current_password"
                        :input-id="id"
                        :feedback="false"
                        toggle-mask
                        autocomplete="current-password"
                        required
                        fluid
                        :invalid="invalid"
                    />
                </FormField>
                <div class="grid gap-5 sm:grid-cols-2">
                    <FormField
                        id="new-password"
                        v-slot="{ id, invalid }"
                        :label="t('security.new')"
                        :hint="t('auth.register.passwordHint')"
                        :error="passwordForm.errors.password"
                    >
                        <Password
                            v-model="passwordForm.password"
                            :input-id="id"
                            :feedback="false"
                            toggle-mask
                            autocomplete="new-password"
                            required
                            fluid
                            :invalid="invalid"
                        />
                    </FormField>
                    <FormField id="confirm-password" v-slot="{ id }" :label="t('security.confirm')">
                        <Password
                            v-model="passwordForm.password_confirmation"
                            :input-id="id"
                            :feedback="false"
                            toggle-mask
                            autocomplete="new-password"
                            required
                            fluid
                        />
                    </FormField>
                </div>
            </SettingsSection>
            <footer
                class="flex justify-end rounded-b-(--radius-lg) border-t border-(--line) bg-(--surface-sunken) px-5 py-3"
            >
                <Button type="submit" :label="t('security.save')" :loading="passwordForm.processing" />
            </footer>
        </form>

        <div class="card">
            <SettingsSection :title="t('security.deleteTitle')" :description="t('security.deleteBody')">
                <div>
                    <Button
                        severity="danger"
                        variant="outlined"
                        :label="t('security.deleteButton')"
                        @click="confirmingDeletion = true"
                    />
                </div>
            </SettingsSection>
        </div>
    </div>

    <Dialog
        v-model:visible="confirmingDeletion"
        modal
        :header="t('security.deleteConfirmTitle')"
        class="w-[min(100%-2rem,28rem)]"
    >
        <form class="grid gap-4" @submit.prevent="deleteAccount">
            <FormField
                id="delete-password"
                v-slot="{ id, invalid }"
                :label="t('security.deleteConfirmBody')"
                :error="deleteForm.errors.password"
            >
                <Password
                    v-model="deleteForm.password"
                    :input-id="id"
                    :feedback="false"
                    toggle-mask
                    autocomplete="current-password"
                    required
                    fluid
                    :invalid="invalid"
                />
            </FormField>
            <div class="flex justify-end gap-2">
                <Button
                    type="button"
                    variant="text"
                    severity="secondary"
                    :label="t('common.cancel')"
                    @click="confirmingDeletion = false"
                />
                <Button
                    type="submit"
                    severity="danger"
                    :label="t('security.deleteConfirmButton')"
                    :loading="deleteForm.processing"
                />
            </div>
        </form>
    </Dialog>
</template>
