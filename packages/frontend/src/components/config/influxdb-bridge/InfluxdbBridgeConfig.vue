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
	<v-form
		ref='form'
		v-slot='{ isValid }'
		:disabled='[ComponentState.Reloading, ComponentState.Action].includes(componentState)'
		@submit.prevent='onSubmit()'
	>
		<ICard>
			<template #title>
				{{ $t('pages.config.influxdb-bridge.title') }}
			</template>
			<template #titleActions>
				<IActionBtn
					:action='Action.Reload'
					container-type='card-title'
					:loading='[ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
					:disabled='componentState === ComponentState.Action'
					@click='getConfig()'
				/>
			</template>
			<v-alert
				v-if='componentState === ComponentState.FetchFailed'
				type='error'
				variant='tonal'
				:text='$t("components.config.influxdb-bridge.messages.fetch.failed")'
			/>
			<v-skeleton-loader
				class='input-skeleton-loader'
				:loading='componentState === ComponentState.Loading'
				type='text, heading@5, text, heading@3'
			>
				<v-responsive>
					<section v-if='config !== null'>
						<legend class='section-legend'>
							{{ $t('components.config.influxdb-bridge.influx.title') }}
						</legend>
						<v-row :no-gutters='display.mobile.value'>
							<v-col
								cols='12'
								md='6'
							>
								<ITextInput
									v-model='config.influx.host'
									:label='$t("common.labels.hostname")'
									:rules='[
										(v: string|null) => ValidationRules.required(v, $t("common.validation.hostname.required")),
										(v: string) => ValidationRules.host(v, $t("common.validation.hostname.invalid")),
									]'
									required
								/>
							</v-col>
							<v-col
								cols='12'
								md='6'
							>
								<INumberInput
									v-model='config.influx.port'
									:label='$t("common.labels.port")'
									:rules='[
										(v: number|null) => ValidationRules.required(v, $t("common.validation.port.required")),
										(v: number) => ValidationRules.integer(v, $t("common.validation.port.integer")),
										(v: number) => ValidationRules.between(v, 1, 65535, $t("common.validation.port.between")),
									]'
									:min='1'
									:max='65535'
									required
								/>
							</v-col>
						</v-row>
						<IPasswordInput
							v-model='config.influx.token'
							:label='$t("components.config.influxdb-bridge.influx.token")'
							:description='$t("components.config.influxdb-bridge.notes.newAuth")'
						/>
						<v-row :no-gutters='display.mobile.value'>
							<v-col
								cols='12'
								md='6'
							>
								<ITextInput
									v-model='config.influx.user'
									:label='$t("common.labels.username")'
									:description='$t("components.config.influxdb-bridge.notes.oldAuth")'
									:prepend-inner-icon='mdiAccount'
								/>
							</v-col>
							<v-col
								cols='12'
								md='6'
							>
								<IPasswordInput
									v-model='config.influx.password'
									:label='$t("common.labels.password")'
								/>
							</v-col>
						</v-row>
						<ITextInput
							v-model='config.influx.org'
							:label='$t("components.config.influxdb-bridge.influx.org")'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("components.config.influxdb-bridge.validation.org.required")),
							]'
							required
							:prepend-inner-icon='mdiDomain'
						/>
						<v-row :no-gutters='display.mobile.value'>
							<v-col
								cols='12'
								md='4'
							>
								<ITextInput
									v-model='config.influx.buckets.gateway'
									:label='$t("components.config.influxdb-bridge.influx.gatewayBucket")'
									:rules='[
										(v: string|null) => ValidationRules.required(v, $t("components.config.influxdb-bridge.validation.gatewayBucket.required")),
									]'
									required
									:prepend-inner-icon='mdiDatabase'
								/>
							</v-col>
							<v-col
								cols='12'
								md='4'
							>
								<ITextInput
									v-model='config.influx.buckets.devices'
									:label='$t("components.config.influxdb-bridge.influx.devicesBucket")'
									:rules='[
										(v: string|null) => ValidationRules.required(v, $t("components.config.influxdb-bridge.validation.devicesBucket.required")),
									]'
									required
									:prepend-inner-icon='mdiDatabase'
								/>
							</v-col>
							<v-col
								cols='12'
								md='4'
							>
								<ITextInput
									v-model='config.influx.buckets.sensors'
									:label='$t("components.config.influxdb-bridge.influx.sensorsBucket")'
									:rules='[
										(v: string|null) => ValidationRules.required(v, $t("components.config.influxdb-bridge.validation.sensorsBucket.required")),
									]'
									required
									:prepend-inner-icon='mdiDatabase'
								/>
							</v-col>
						</v-row>
						<legend class='section-legend'>
							{{ $t('components.config.influxdb-bridge.mqtt.title') }}
						</legend>
						<v-row :no-gutters='display.mobile.value'>
							<v-col
								cols='12'
								md='6'
							>
								<ITextInput
									v-model='config.mqtt.host'
									:label='$t("common.labels.hostname")'
									:rules='[
										(v: string|null) => ValidationRules.required(v, $t("common.validation.hostname.required")),
										(v: string) => ValidationRules.host(v, $t("common.validation.hostname.required")),
									]'
									required
								/>
							</v-col>
							<v-col
								cols='12'
								md='6'
							>
								<INumberInput
									v-model.number='config.mqtt.port'
									:label='$t("common.labels.port")'
									:rules='[
										(v: number|null) => ValidationRules.required(v, $t("common.validation.port.required")),
										(v: number) => ValidationRules.integer(v, $t("common.validation.port.integer")),
										(v: number) => ValidationRules.between(v, 1, 65535, $t("common.validation.port.between")),
									]'
									:min='1'
									:max='65535'
									required
								/>
							</v-col>
						</v-row>
						<ITextInput
							v-model='config.mqtt.client'
							:label='$t("components.config.daemon.connections.mqtt.clientId")'
							:rules='[
								(v: number|null) => ValidationRules.required(v, $t("components.config.daemon.connections.mqtt.validation.clientIdMissing")),
							]'
							required
							:prepend-inner-icon='mdiIdentifier'
						/>
						<v-row :no-gutters='display.mobile.value'>
							<v-col
								cols='12'
								md='6'
							>
								<ITextInput
									v-model='config.mqtt.user'
									:label='$t("common.labels.username")'
									:rules='[
										(v: string|null) => ValidationRules.required(v, $t("common.validation.username.required")),
									]'
									required
									:prepend-inner-icon='mdiAccount'
								/>
							</v-col>
							<v-col
								cols='12'
								md='6'
							>
								<IPasswordInput
									v-model='config.mqtt.password'
									:label='$t("common.labels.password")'
									:rules='[
										(v: string|null) => ValidationRules.required(v, $t("common.validation.password.required")),
									]'
									required
								/>
							</v-col>
						</v-row>
						<IDataTable
							:headers='headers'
							:items='config.mqtt.topics'
							no-data-text='components.config.influxdb-bridge.mqtt.noTopics'
							:dense='true'
							:hover='true'
						>
							<template #top>
								<v-toolbar density='compact' rounded>
									<v-toolbar-title>
										{{ $t('components.config.influxdb-bridge.topics.title') }}
									</v-toolbar-title>
									<v-toolbar-items>
										<SubscriptionTopicForm
											:action='Action.Add'
											:disabled='[ComponentState.Action, ComponentState.Reloading].includes(componentState)'
											@save='(index: number|undefined, t: string) => saveTopic(index, t)'
										/>
										<IActionBtn
											v-tooltip:bottom='$t("components.config.influxdb-bridge.topics.actions.deleteAll")'
											:action='Action.Delete'
											container-type='card-title'
											:disabled='[ComponentState.Action, ComponentState.Reloading].includes(componentState)'
											@click='clearTopics()'
										/>
									</v-toolbar-items>
								</v-toolbar>
							</template>
							<template #item.topic='{ item }'>
								{{ item }}
							</template>
							<template #item.actions='{ item, index }'>
								<SubscriptionTopicForm
									:action='Action.Edit'
									:index='index'
									:topic='item'
									:disabled='[ComponentState.Action, ComponentState.Reloading].includes(componentState)'
									@save='(index: number, t: string) => saveTopic(index, t)'
								/>
								<IDataTableAction
									:action='Action.Delete'
									:tooltip='$t("components.config.influxdb-bridge.topics.actions.delete")'
									:disabled='[ComponentState.Action, ComponentState.Reloading].includes(componentState)'
									@click='removeTopic(index)'
								/>
							</template>
						</IDataTable>
					</section>
				</v-responsive>
			</v-skeleton-loader>
			<template #actions>
				<IActionBtn
					:action='Action.Save'
					:loading='componentState === ComponentState.Action'
					:disabled='!isValid.value || [ComponentState.Loading, ComponentState.Reloading].includes(componentState)'
					type='submit'
				/>
			</template>
		</ICard>
	</v-form>
</template>

<script lang='ts' setup>
import { type IqrfGatewayInfluxdbBridgeService } from '@iqrf/iqrf-gateway-webapp-client/services/Config';
import { type BridgeConfig } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import {
	Action,
	ComponentState,
	IActionBtn,
	ICard,
	IDataTable,
	IDataTableAction,
	INumberInput,
	IPasswordInput,
	ITextInput,
	ValidationRules,
} from '@iqrf/iqrf-vue-ui';
import { mdiAccount, mdiDatabase, mdiDomain, mdiIdentifier } from '@mdi/js';
import { onMounted, ref, type Ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue3-toastify';
import { useDisplay } from 'vuetify';
import { VForm } from 'vuetify/components';

import SubscriptionTopicForm from '@/components/config/influxdb-bridge/SubscriptionTopicForm.vue';
import { validateForm } from '@/helpers/validateForm';
import { useApiClient } from '@/services/ApiClient';

const componentState: Ref<ComponentState> = ref(ComponentState.Created);
const i18n = useI18n();
const display = useDisplay();
const service: IqrfGatewayInfluxdbBridgeService = useApiClient().getConfigServices().getIqrfGatewayInfluxdbBridgeService();
const form: Ref<VForm | null> = ref(null);
const config: Ref<BridgeConfig | null> = ref(null);
const headers = [
	{ key: 'topic', title: i18n.t('components.config.influxdb-bridge.topics.topic') },
	{ key: 'actions', title: i18n.t('common.columns.actions'), align: 'end' },
];

async function getConfig(): Promise<void> {
	componentState.value = [
		ComponentState.Created,
		ComponentState.FetchFailed,
	].includes(componentState.value) ? ComponentState.Loading : ComponentState.Reloading;
	try {
		config.value = await service.getConfig();
		componentState.value = ComponentState.Ready;
	} catch {
		toast.error(
			i18n.t('components.config.influxdb-bridge.messages.fetch.failed'),
		);
		componentState.value = componentState.value === ComponentState.Loading ? ComponentState.FetchFailed : ComponentState.Ready;
	}
}

async function onSubmit(): Promise<void> {
	if (!await validateForm(form.value) || config.value === null) {
		return;
	}
	componentState.value = ComponentState.Action;
	const params = { ...config.value };
	try {
		await service.updateConfig(params);
		toast.success(
			i18n.t('components.config.influxdb-bridge.messages.save.success'),
		);
	} catch {
		toast.error(
			i18n.t('components.config.influxdb-bridge.messages.save.failed'),
		);
	}
	componentState.value = ComponentState.Ready;
}

onMounted(() => {
	getConfig();
});

function saveTopic(index: number|undefined, topic: string) {
	if (index === undefined) {
		if (config.value !== null) {
			config.value.mqtt.topics.push(topic);
		}
	} else if (config.value !== null) {
		config.value.mqtt.topics[index] = topic;
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
