<template>
	<v-dialog
		v-model='show'
		scrollable
		persistent
		no-click-animation
		:width='width'
	>
		<template #activator='{ props }'>
			<v-btn
				id='import-activator'
				v-bind='props'
				color='white'
				size='large'
				:icon='mdiImport'
			/>
			<v-tooltip
				activator='#import-activator'
				location='bottom'
			>
				{{ $t('components.configuration.daemon.connections.actions.import') }}
			</v-tooltip>
		</template>
		<v-form
			ref='form'
			v-slot='{ isValid }'
			:disabled='componentState === ComponentState.Saving'
		>
			<Card>
				<template #title>
					{{ $t('components.configuration.daemon.connections.udp.import.title') }}
				</template>
				<v-file-input
					v-model='files'
					accept='.json'
					:label='$t("components.configuration.daemon.connections.profileFile")'
					:rules='[
						(v: File|Blob|null) => ValidationRules.required(v, $t("components.configuration.daemon.connections.validation.profileFileMissing")),
					]'
					:prepend-inner-icon='mdiFileOutline'
					:prepend-icon='null'
					show-size
					required
				/>
				<template #actions>
					<v-btn
						color='primary'
						variant='elevated'
						:disabled='!isValid.value || componentState === ComponentState.Saving'
						@click='onSubmit'
					>
						{{ $t('common.buttons.import') }}
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
	</v-dialog>
</template>

<script lang='ts' setup>
import { IqrfGatewayDaemonComponentName, type IqrfGatewayDaemonUdpMessaging } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { mdiFileOutline, mdiImport } from '@mdi/js';
import { ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import Card from '@/components/Card.vue';
import { getModalWidth } from '@/helpers/modal';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Ready);
const emit = defineEmits(['import']);
const i18n = useI18n();
const width = getModalWidth();
const show: Ref<boolean> = ref(false);
const form: Ref<typeof VForm | null> = ref(null);
const files: Ref<File[]> = ref([]);

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value) || files.value.length === 0) {
		return;
	}
	componentState.value = ComponentState.Saving;
	const file = files.value[0];
	const content = await file.text();
	const obj = JSON.parse(content);
	if (!isUdpConnectionProfile(obj)) {
		toast.error(
			i18n.t('components.configuration.daemon.connections.messages.profileFileInvalid'),
		);
		componentState.value = ComponentState.Ready;
		return;
	}
	componentState.value = ComponentState.Ready;
	emit('import', obj);
	close();
}

function isUdpConnectionProfile(obj: any): obj is IqrfGatewayDaemonUdpMessaging {
	return obj.component !== undefined && typeof(obj.component) === 'string' && obj.component === IqrfGatewayDaemonComponentName.IqrfUdpMessaging &&
		obj.instance !== undefined && typeof(obj.instance) === 'string' &&
		obj.LocalPort !== undefined && typeof(obj.LocalPort) === 'number' &&
		obj.RemotePort !== undefined && typeof(obj.RemotePort) === 'number' &&
		obj.deviceRecordExpiration !== undefined && typeof(obj.deviceRecordExpiration) === 'number';
}

function close(): void {
	files.value = [];
	show.value = false;
}

</script>
