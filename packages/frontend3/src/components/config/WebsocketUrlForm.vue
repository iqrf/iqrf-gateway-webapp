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
	<ModalWindow v-model='show'>
		<template #activator='{ props }'>
			<v-btn
				v-bind='props'
				color='primary'
				:prepend-icon='mdiPencil'
			>
				{{ $t("components.common.actions.edit") }}
			</v-btn>
		</template>
		<v-form
			ref='form'
			v-slot='{ isValid }'
			@submit.prevent='onSubmit'
		>
			<Card>
				<template #title>
					{{ cardTitle }}
				</template>
				<SelectInput
					v-model='data.protocol'
					:label='$t("common.labels.protocol")'
					:items='protocolOptions'
				/>
				<TextInput
					v-model='data.hostname'
					:label='$t("common.labels.hostname")'
					:rules='[
						(v: string|null) => ValidationRules.required(v, $t("common.validation.hostnameMissing")),
						(v: string) => ValidationRules.server(v, $t("common.validation.hostnameInvalid")),
					]'
					required
				/>
				<NumberInput
					v-model.number='data.port'
					:label='$t("common.labels.port")'
					:min='1'
					:max='65535'
					:rules='[
						(v: number|null) => ValidationRules.required(v, $t("common.validation.portMissing")),
						(v: number) => ValidationRules.integer(v, $t("common.validation.portInvalid")),
						(v: number) => ValidationRules.between(v, 1, 65535, $t("common.validation.portInvalid")),
					]'
					required
				/>
				<TextInput
					v-model='data.path'
					:label='$t("common.labels.path")'
				/>
				<template #actions>
					<CardActionBtn
						:action='Action.Edit'
						:disabled='!isValid.value'
						type='submit'
					/>
					<v-spacer />
					<CardActionBtn
						:action='Action.Cancel'
						@click='close'
					/>
				</template>
			</Card>
		</v-form>
	</ModalWindow>
</template>

<script lang='ts' setup>
import { mdiPencil } from '@mdi/js';
import { type Ref, ref } from 'vue';
import { watchEffect } from 'vue';
import { useI18n } from 'vue-i18n';

import Card from '@/components/layout/card/Card.vue';
import CardActionBtn from '@/components/layout/card/CardActionBtn.vue';
import NumberInput from '@/components/layout/form/NumberInput.vue';
import SelectInput from '@/components/layout/form/SelectInput.vue';
import TextInput from '@/components/layout/form/TextInput.vue';
import ModalWindow from '@/components/ModalWindow.vue';
import ValidationRules from '@/helpers/ValidationRules';
import { Action } from '@/types/Action';
import { Protocol, type ParsedUrl, UrlHelper } from '@/types/Url';

const componentProps = defineProps({
	cardTitle: {
		type: String,
		default: '',
		required: false,
	},
	url: {
		type: String,
		required: true,
	},
});
const emit = defineEmits(['edited']);
const i18n = useI18n();
const show: Ref<boolean> = ref(false);
const protocolOptions = [
	{
		title: i18n.t('common.labels.protocols.ws'),
		value: Protocol.WS,
	},
	{
		title: i18n.t('common.labels.protocols.wss'),
		value: Protocol.WSS,
	},
];
const data: Ref<ParsedUrl> = ref({
	protocol: Protocol.WS,
	hostname: '',
	port: null,
	path: null,
});

watchEffect(async (): Promise<void> => {
	if (show.value) {
		try {
			data.value = UrlHelper.fromString(componentProps.url);
		} catch (error) {
			console.error(error);
		}
	}
});

async function onSubmit(): Promise<void> {
	emit('edited', UrlHelper.toString(data.value));
	close();
}

function close(): void {
	show.value = false;
}

</script>
