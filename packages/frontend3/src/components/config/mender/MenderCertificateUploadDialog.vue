<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software,
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied
See the License for the specific language governing permissions and
limitations under the License.
-->

<template>
	<ModalWindow
		v-model='show'
	>
		<template #activator='{ props }'>
			<v-btn
				v-bind='props'
				color='primary'
			>
				{{ $t('common.buttons.upload') }}
			</v-btn>
		</template>
		<v-form
			ref='form'
			v-slot='{ isValid }'
			:disabled='componentState === ComponentState.Saving'
		>
			<Card>
				<template #title>
					{{ $t('components.configuration.mender.upload.title') }}
				</template>
				<v-file-input
					v-model='certificates'
					accept='.pem'
					:label='$t("components.configuration.mender.upload.cert")'
					:rules='[
						(v: File|Blob|null) => ValidationRules.required(v, $t("components.configuration.mender.validation.certificateMissing")),
					]'
					:prepend-inner-icon='mdiFileOutline'
					:prepend-icon='null'
					show-size
					required
				/>
				<TextInput
					v-if='certPath.length > 0'
					v-model='certPath'
					:label='$t("components.configuration.mender.upload.certPath")'
					readonly
				>
					<template #append-inner>
						<v-btn
							color='primary'
							@click='copyToClipboard(certPath)'
						>
							<v-icon :icon='mdiClipboard' />
							{{ $t('common.buttons.clipboard') }}
						</v-btn>
					</template>
				</TextInput>
				<template #actions>
					<v-btn
						color='primary'
						variant='elevated'
						:disabled='!isValid.value || componentState === ComponentState.Saving'
						@click='onSubmit'
					>
						{{ $t('common.buttons.upload') }}
					</v-btn>
					<v-spacer />
					<v-btn
						color='grey-darken-2'
						variant='elevated'
						@click='close'
					>
						{{ $t('common.buttons.close') }}
					</v-btn>
				</template>
			</Card>
		</v-form>
	</ModalWindow>
</template>

<script lang='ts' setup>
import { type MenderService } from '@iqrf/iqrf-gateway-webapp-client/services';
import { mdiClipboard, mdiFileOutline } from '@mdi/js';
import { ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import Card from '@/components/Card.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import TextInput from '@/components/TextInput.vue';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const show: Ref<boolean> = ref(false);
const i18n = useI18n();
const form: Ref<typeof VForm | null> = ref(null);
const service: MenderService = useApiClient().getMenderService();
const certificates: Ref<File[]> = ref([]);
const certPath: Ref<string> = ref('');

function copyToClipboard(content: string): void {
	navigator.clipboard.writeText(content);
}

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value) || certificates.value.length === 0) {
		return;
	}
	componentState.value = ComponentState.Saving;
	const certificate = certificates.value[0];
	service.uploadCert(certificate)
		.then((response: string) => {
			certPath.value = response;
			toast.success(
				i18n.t('components.configuration.mender.messages.upload.success'),
			);
			componentState.value = ComponentState.Ready;
		})
		.catch(() => toast.error('TODO SAVE ERROR HANDLING'));
}

function close(): void {
	show.value = false;
}
</script>
