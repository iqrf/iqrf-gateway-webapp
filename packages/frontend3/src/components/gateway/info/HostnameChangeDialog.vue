<template>
	<v-dialog
		v-model='show'
		persistent
		scrollable
		no-click-animation
		:width='width'
	>
		<template #activator='{ props }'>
			<v-btn
				color='primary'
				size='x-small'
				v-bind='props'
			>
				<v-icon :icon='mdiPencil' />
				{{ $t('common.buttons.edit') }}
			</v-btn>
		</template>
		<Card>
			<template #title>
				{{ $t('components.gateway.information.hostnameChange.title') }}
			</template>
			<v-form ref='form'>
				<TextInput
					v-model='hostname'
					:label='$t("components.gateway.information.hostname")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("components.gateway.information.hostnameChange.validation.hostname")),
					]'
					required
				/>
				<v-checkbox
					v-model='setSplitterId'
					:label='$t("components.gateway.information.hostnameChange.setSplitterId")'
					density='compact'
				/>
				<v-checkbox
					v-model='setIdeHostname'
					:label='$t("components.gateway.information.hostnameChange.setIdeHostname")'
					density='compact'
				/>
			</v-form>
			<template #actions>
				<v-btn
					color='primary'
					variant='elevated'
					@click='onSubmit'
				>
					{{ $t('common.buttons.save') }}
				</v-btn>
				<v-spacer />
				<v-btn
					color='grey-darken-2'
					variant='elevated'
					@click='close'
				>
					{{ $t('common.buttons.cancel') }}
				</v-btn>
			</template>
		</Card>
	</v-dialog>
</template>

<script lang='ts' setup>
import { PropType, ref, Ref, watchEffect } from 'vue';
import { VForm } from 'vuetify/components';

import Card from '@/components/Card.vue';
import TextInput from '@/components/TextInput.vue';

import { mdiPencil } from '@mdi/js';
import { getModalWidth } from '@/helpers/modal';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { HostnameService } from '@iqrf/iqrf-gateway-webapp-client/services/Gateway';
import { useApiClient } from '@/services/ApiClient';
import { toast } from 'vue3-toastify';
import { useI18n } from 'vue-i18n';
import { IqrfGatewayDaemonService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { AxiosResponse } from 'axios';
import { IqrfGatewayDaemonComponent, IqrfGatewayDaemonIdeCounterpart, IqrfGatewayDaemonJsonSplitter } from '@iqrf/iqrf-gateway-webapp-client/types/Config';

const emit = defineEmits(['saved']);
const props = defineProps({
	currentHostname: {
		type: [String, null] as PropType<string | null>,
		required: true,
	}
});
const i18n = useI18n();
const hostnameService: HostnameService = useApiClient().getGatewayServices().getHostnameService();
const daemonConfigurationService: IqrfGatewayDaemonService = useApiClient().getConfigServices().getIqrfGatewayDaemonService();
const width = getModalWidth();
const show: Ref<boolean> = ref(false);
const form: Ref<typeof VForm | null> = ref(null);
const hostname: Ref<string> = ref('');
const setSplitterId: Ref<boolean> = ref(false);
const setIdeHostname: Ref<boolean> = ref(false);
const ideComponentComponent = 'iqrf::IdeCounterpart';
const splitterComponent = 'iqrf::JsonSplitter';

watchEffect((): void => {
	if (props.currentHostname === null) {
		hostname.value = '';
	} else {
		hostname.value = props.currentHostname.split('.', 1)[0] ?? '';
	}
});

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value)) {
		return;
	}
	Promise.all([
		updateSplitterConfig(),
		updateIdeCounterpartConfig(),
		hostnameService.setHostname(hostname.value),
	])
		.then(() => {
			toast.success(
				i18n.t('components.gateway.information.hostnameChange.messages.save.success')
			);
			if (setSplitterId.value || setIdeHostname.value) {
				toast.success(
					i18n.t('components.gateway.information.hostnameChange.messages.daemonRestart')
				);
			}
			clear();
			close();
			emit('saved');
		})
		.catch(() => {
			toast.error('TODO ERROR HANDLING');
		});
}

async function updateSplitterConfig(): Promise<void> {
	if (!setSplitterId.value) {
		return;
	}
	const instance: IqrfGatewayDaemonJsonSplitter = await daemonConfigurationService.getComponent(splitterComponent)
		.then((response: IqrfGatewayDaemonComponent) => (response.instances as IqrfGatewayDaemonJsonSplitter[])[0] ?? null);
	if (instance === null) {
		return;
	}
	instance.insId = hostname.value;
	await daemonConfigurationService.updateInstance(splitterComponent, instance.instance, instance);
}

async function updateIdeCounterpartConfig(): Promise<void> {
	if (!setIdeHostname.value) {
		return;
	}
	const instance: IqrfGatewayDaemonIdeCounterpart = await daemonConfigurationService.getComponent(ideComponentComponent)
		.then((response: IqrfGatewayDaemonComponent) => (response.instances as IqrfGatewayDaemonIdeCounterpart[])[0] ?? null);
	if (instance === null) {
		return;
	}
	instance.gwIdentName = hostname.value;
	await daemonConfigurationService.updateInstance(ideComponentComponent, instance.instance, instance);
}

function clear(): void {
	setSplitterId.value = false;
	setIdeHostname.value = false;
}

function close(): void {
	show.value = false;
}

</script>
