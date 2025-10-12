<!--
Copyright 2017-2025 IQRF Tech s.r.o.
Copyright 2019-2025 MICRORISC s.r.o.

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
	<IModalWindow
		v-model='show'
		persistent
	>
		<template #activator='{ props }'>
			<v-btn
				v-bind='props'
				color='primary'
			>
				{{ $t('components.common.actions.upload') }}
			</v-btn>
		</template>
		<v-form
			ref='form'
			v-slot='{ isValid }'
			:disabled='componentState === ComponentState.Action'
			@submit.prevent='onSubmit()'
		>
			<ICard>
				<template #title>
					{{ $t('components.config.mender.upload.title') }}
				</template>
				<v-file-input
					v-model='certificates'
					accept='.pem'
					:label='$t("components.config.mender.upload.cert")'
					:rules='[
						(v: File|Blob|null) => ValidationRules.required(v, $t("components.config.mender.validation.certificate.required")),
					]'
					:prepend-inner-icon='mdiFileOutline'
					:prepend-icon='undefined'
					show-size
					required
				/>
				<ITextInput
					v-if='certPath.length > 0'
					v-model='certPath'
					:label='$t("components.config.mender.upload.certPath")'
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
				</ITextInput>
				<template #actions>
					<IActionBtn
						:action='Action.Upload'
						:loading='componentState === ComponentState.Action'
						:disabled='!isValid.value'
						type='submit'
					/>
					<v-spacer />
					<IActionBtn
						:action='Action.Cancel'
						:disabled='componentState === ComponentState.Action'
						@click='close()'
					/>
				</template>
			</ICard>
		</v-form>
	</IModalWindow>
</template>

<script lang='ts' setup>
import { type MenderService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	IModalWindow,
	ITextInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import { mdiClipboard, mdiFileOutline } from '@mdi/js';
import { ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';

const emit = defineEmits(['path']);
const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const show: Ref<boolean> = ref(false);
const i18n = useI18n();
const form: Ref<VForm | null> = ref(null);
const service: MenderService = useApiClient().getConfigServices().getMenderService();
const certificates: Ref<File | null> = ref(null);
const certPath: Ref<string> = ref('');

function copyToClipboard(content: string): void {
	navigator.clipboard.writeText(content);
}

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value) || certificates.value === null) {
		return;
	}
	componentState.value = ComponentState.Action;
	const certificate = certificates.value;
	try {
		certPath.value = await service.uploadCert(certificate);
		toast.success(
			i18n.t('components.config.mender.messages.upload.success'),
		);
		close();
		emit('path', certPath.value);
	} catch {
		toast.error(
			i18n.t('components.config.mender.messages.upload.failed'),
		);
	}
	componentState.value = ComponentState.Ready;
}

function close(): void {
	show.value = false;
}
</script>
