<template>
	<ModalWindow
		v-model='show'
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
					{{ $t('components.configuration.daemon.connections.mqtt.import.title') }}
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
	</ModalWindow>
</template>

<script lang='ts' setup>
import { IqrfGatewayDaemonComponentName, type IqrfGatewayDaemonMqttMessaging } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { mdiFileOutline, mdiImport } from '@mdi/js';
import { ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import Card from '@/components/Card.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Ready);
const emit = defineEmits(['import']);
const i18n = useI18n();
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
	if (!isMqttConnectionProfile(obj)) {
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

function isMqttConnectionProfile(obj: any): obj is IqrfGatewayDaemonMqttMessaging {
	return obj.component !== undefined && typeof(obj.component) === 'string' && obj.component === IqrfGatewayDaemonComponentName.IqrfMqttMessaging &&
		obj.instance !== undefined && typeof(obj.instance) === 'string' &&
		obj.BrokerAddr !== undefined && typeof(obj.BrokerAddr) === 'string' &&
		obj.ClientId !== undefined && typeof(obj.ClientId) === 'string' &&
		obj.ConnectTimeout !== undefined && typeof(obj.ConnectTimeout) === 'number' &&
		obj.EnableServerCertAuth !== undefined && typeof(obj.EnableServerCertAuth) === 'boolean' &&
		obj.EnabledCipherSuites !== undefined && typeof(obj.EnabledCipherSuites) === 'string' &&
		obj.EnabledSSL !== undefined && typeof(obj.EnabledSSL) === 'boolean' &&
		obj.KeepAliveInterval !== undefined && typeof(obj.KeepAliveInterval) === 'number' &&
		obj.KeyStore !== undefined && typeof(obj.KeyStore) === 'string' &&
		obj.MaxReconnect !== undefined && typeof(obj.MaxReconnect) === 'number' &&
		obj.MinReconnect !== undefined && typeof(obj.MinReconnect) === 'number' &&
		obj.Password !== undefined && typeof(obj.Password) === 'string' &&
		obj.Persistence !== undefined && typeof(obj.Persistence) === 'number' &&
		obj.PrivateKey !== undefined && typeof(obj.PrivateKey) === 'string' &&
		obj.PrivateKeyPassword !== undefined && typeof(obj.PrivateKeyPassword) === 'string' &&
		obj.Qos !== undefined && typeof(obj.Qos) === 'number' &&
		obj.TopicRequest !== undefined && typeof(obj.TopicRequest) === 'string' &&
		obj.TopicResponse !== undefined && typeof(obj.TopicResponse) === 'string' &&
		obj.TrustStore !== undefined && typeof(obj.TrustStore) === 'string' &&
		obj.User !== undefined && typeof(obj.User) === 'string' &&
		obj.acceptAsyncMsg !== undefined && typeof(obj.acceptAsyncMsg) === 'boolean';
}

function close(): void {
	files.value = [];
	show.value = false;
}

</script>