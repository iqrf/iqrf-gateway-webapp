<template>
	<v-form
		ref='form'
		v-slot='{ isValid, validate }'
		:disabled='[ComponentState.Reloading, ComponentState.Saving].includes(componentState)'
	>
		<Card>
			<template #title>
				{{ $t('pages.configuration.influxdb-bridge.title') }}
			</template>
			<template #titleActions>
				<v-btn
					id='reload-activator'
					color='white'
					:icon='mdiReload'
					@click='getConfig'
				/>
			</template>
			<v-skeleton-loader
				class='input-skeleton-loader'
				:loading='componentState === ComponentState.Loading'
				type='text, heading@6, text, heading@3, text, table-heading, table-row-divider@2, table-row'
			>
				<v-responsive>
					<section v-if='config'>
						<legend class='section-legend'>
							{{ $t('components.configuration.influxdb-bridge.influx') }}
						</legend>
						<v-row>
							<v-col
								cols='12'
								md='6'
							>
								<TextInput
									v-model='config.influx.host'
									:label='$t("common.labels.hostname")'
									:rules='[
										(v: string|null) => ValidationRules.required(v, $t("common.validation.hostnameMissing")),
										(v: string) => ValidationRules.server(v, $t("common.validation.hostnameInvalid")),
									]'
									required
								/>
							</v-col>
							<v-col
								cols='12'
								md='6'
							>
								<TextInput
									v-model.number='config.influx.port'
									type='number'
									:label='$t("common.labels.port")'
									:rules='[
										(v: number|null) => ValidationRules.required(v, $t("common.validation.portMissing")),
										(v: number) => ValidationRules.integer(v, $t("common.validation.portInvalid")),
										(v: number) => ValidationRules.between(v, 1, 65535, $t("common.validation.portInvalid")),
									]'
									required
								/>
							</v-col>
						</v-row>
						<TextInput
							v-model='config.influx.token'
							:label='$t("components.configuration.influxdb-bridge.token")'
							:description='$t("components.configuration.influxdb-bridge.notes.newAuth")'
						/>
						<v-row>
							<v-col
								cols='12'
								md='6'
							>
								<TextInput
									v-model='config.influx.user'
									:label='$t("common.labels.username")'
									:description='$t("components.configuration.influxdb-bridge.notes.oldAuth")'
								/>
							</v-col>
							<v-col
								cols='12'
								md='6'
							>
								<PasswordInput
									v-model='config.influx.password'
									:label='$t("common.labels.password")'
								/>
							</v-col>
						</v-row>
						<TextInput
							v-model='config.influx.org'
							:label='$t("components.configuration.influxdb-bridge.org")'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("components.configuration.influxdb-bridge.validation.orgMissing")),
							]'
							required
						/>
						<v-row>
							<v-col
								cols='12'
								md='4'
							>
								<TextInput
									v-model='config.influx.buckets.gateway'
									:label='$t("components.configuration.influxdb-bridge.gatewayBucket")'
									:rules='[
										(v: string|null) => ValidationRules.required(v, $t("components.configuration.influxdb-bridge.validation.gatewayBucketMissing")),
									]'
									required
								/>
							</v-col>
							<v-col
								cols='12'
								md='4'
							>
								<TextInput
									v-model='config.influx.buckets.devices'
									:label='$t("components.configuration.influxdb-bridge.devicesBucket")'
									:rules='[
										(v: string|null) => ValidationRules.required(v, $t("components.configuration.influxdb-bridge.validation.devicesBucketMissing")),
									]'
									required
								/>
							</v-col>
							<v-col
								cols='12'
								md='4'
							>
								<TextInput
									v-model='config.influx.buckets.sensors'
									:label='$t("components.configuration.influxdb-bridge.sensorsBucket")'
									:rules='[
										(v: string|null) => ValidationRules.required(v, $t("components.configuration.influxdb-bridge.validation.sensorsBucketMissing")),
									]'
									required
								/>
							</v-col>
						</v-row>
						<legend class='section-legend'>
							{{ $t('components.configuration.influxdb-bridge.mqtt') }}
						</legend>
						<v-row>
							<v-col
								cols='12'
								md='6'
							>
								<TextInput
									v-model='config.mqtt.host'
									:label='$t("common.labels.hostname")'
									:rules='[
										(v: string|null) => ValidationRules.required(v, $t("common.validation.hostnameMissing")),
										(v: string) => ValidationRules.server(v, $t("common.validation.hostnameInvalid")),
									]'
									required
								/>
							</v-col>
							<v-col
								cols='12'
								md='6'
							>
								<TextInput
									v-model.number='config.mqtt.port'
									type='number'
									:label='$t("common.labels.port")'
									:rules='[
										(v: number|null) => ValidationRules.required(v, $t("common.validation.portMissing")),
										(v: number) => ValidationRules.integer(v, $t("common.validation.portInvalid")),
										(v: number) => ValidationRules.between(v, 1, 65535, $t("common.validation.portInvalid")),
									]'
									required
								/>
							</v-col>
						</v-row>
						<TextInput
							v-model='config.mqtt.client'
							:label='$t("components.configuration.daemon.connections.mqtt.clientId")'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("components.configuration.daemon.connections.mqtt.validation.clientIdMissing")),
							]'
							required
						/>
						<v-row>
							<v-col
								cols='12'
								md='6'
							>
								<TextInput
									v-model='config.mqtt.user'
									:label='$t("common.labels.username")'
									:rules='config.mqtt.password.length > 0 ?
										[
											(v: string|null) => ValidationRules.required(v, $t("common.validation.credentialsMissing")),
										] : []
									'
									:required='config.mqtt.password.length > 0'
									@change='validate'
								/>
							</v-col>
							<v-col
								cols='12'
								md='6'
							>
								<PasswordInput
									v-model='config.mqtt.password'
									:label='$t("common.labels.password")'
									:rules='config.mqtt.user.length > 0 ?
										[
											(v: string|null) => ValidationRules.required(v, $t("common.validation.credentialsMissing")),
										] : []
									'
									:required='config.mqtt.user.length > 0'
									@change='validate'
								/>
							</v-col>
						</v-row>
						<DataTable
							:headers='headers'
							:items='config.mqtt.topics'
							no-data-text='components.configuration.influxdb-bridge.noTopics'
							:dense='true'
							:hover='true'
						>
							<template #top>
								<v-toolbar density='compact' rounded>
									<v-toolbar-title>
										{{ $t('components.configuration.influxdb-bridge.topicTitle') }}
									</v-toolbar-title>
									<v-toolbar-items>
										<SubscriptionTopicForm
											:action='FormAction.Add'
											@save='saveTopic'
										/>
										<v-tooltip
											location='bottom'
										>
											<template #activator='{ props }'>
												<v-btn
													v-bind='props'
													color='error'
													:icon='mdiDelete'
													@click='clearTopics'
												/>
											</template>
											{{ $t('components.configuration.influxdb-bridge.actions.deleteAll') }}
										</v-tooltip>
									</v-toolbar-items>
								</v-toolbar>
							</template>
							<template #item.topic='{ item }'>
								{{ item }}
							</template>
							<template #item.actions='{ item, index }'>
								<SubscriptionTopicForm
									:action='FormAction.Edit'
									:index='index'
									:topic='item'
									@save='saveTopic'
								/>
								<v-tooltip
									location='bottom'
								>
									<template #activator='{ props }'>
										<v-icon
											v-bind='props'
											color='error'
											size='large'
											@click='removeTopic(index)'
										>
											{{ mdiDelete }}
										</v-icon>
									</template>
									{{ $t('components.configuration.influxdb-bridge.actions.delete') }}
								</v-tooltip>
							</template>
						</DataTable>
					</section>
				</v-responsive>
			</v-skeleton-loader>
			<template #actions>
				<v-btn
					color='primary'
					variant='elevated'
					:disabled='componentState !== ComponentState.Ready || !isValid.value'
					@click='onSubmit'
				>
					{{ $t('common.buttons.save') }}
				</v-btn>
			</template>
		</Card>
	</v-form>
</template>

<script lang='ts' setup>
import { type IqrfGatewayInfluxdbBridgeService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { type BridgeConfig } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { mdiDelete, mdiReload } from '@mdi/js';
import {
	onMounted,
	type Ref,
	ref,
} from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { VForm } from 'vuetify/components';

import Card from '@/components/Card.vue';
import SubscriptionTopicForm from '@/components/config/influxdb-bridge/SubscriptionTopicForm.vue';
import DataTable from '@/components/DataTable.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextInput from '@/components/TextInput.vue';
import { FormAction } from '@/enums/controls';
import { validateForm } from '@/helpers/validateForm';
import ValidationRules from '@/helpers/ValidationRules';
import { useApiClient } from '@/services/ApiClient';
import { ComponentState } from '@/types/ComponentState';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const service: IqrfGatewayInfluxdbBridgeService = useApiClient().getConfigServices().getIqrfGatewayInfluxdbBridgeService();
const form: Ref<typeof VForm | null> = ref(null);
const config: Ref<BridgeConfig | null> = ref(null);
const headers = [
	{key: 'topic', title: i18n.t('components.configuration.influxdb-bridge.topic')},
	{key: 'actions', title: i18n.t('common.columns.actions'), align: 'end'},
];

async function getConfig(): Promise<void> {
	if (componentState.value === ComponentState.Created) {
		componentState.value = ComponentState.Loading;
	} else {
		componentState.value = ComponentState.Reloading;
	}
	service.fetchConfig()
		.then((response: BridgeConfig) => {
			config.value = response;
			componentState.value = ComponentState.Ready;
		})
		.catch(() => {
			componentState.value = ComponentState.FetchFailed;
			toast.error('TODO FETCH ERROR HANDLING');
		});
}

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value) || config.value === null) {
		return;
	}
	componentState.value = ComponentState.Saving;
	const params = {...config.value};
	service.editConfig(params)
		.then(() => getConfig().then(() => {
			toast.success(
				i18n.t('components.configuration.influxdb-bridge.messages.save.success'),
			);
		}))
		.catch(() => toast.error('TODO SAVE ERROR HANDLING'));
}

onMounted(() => {
	getConfig();
});

function saveTopic(index: number|undefined, topic: string) {
	if (index === undefined) {
		if (config.value !== null) {
			config.value.mqtt.topics.push(topic);
		}
	} else {
		if (config.value !== null) {
			config.value.mqtt.topics[index] = topic;
		}
	}
}

function removeTopic(index: number): void {
	if (config.value !== null) {
		config.value.mqtt.topics.splice(index, 1);
	}
}

function clearTopics(): void {
	if (config.value !== null) {
		config.value.mqtt.topics = [];
	}
}
</script>
