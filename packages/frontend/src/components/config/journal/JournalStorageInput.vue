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
	<ISelectInput
		v-model='modelValue'
		:label='$t("components.config.journal.storage")'
		:items='options'
		:prepend-inner-icon='mdiMemory'
	/>
</template>

<script setup lang='ts'>
import { JournalPersistence } from '@iqrf/iqrf-gateway-webapp-client/types/Config';
import { ISelectInput } from '@iqrf/iqrf-vue-ui';
import { mdiMemory } from '@mdi/js';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

import { SelectItem } from '@/types/vuetify';

const modelValue = defineModel<JournalPersistence>({
	required: true,
});

const i18n = useI18n();
const options = computed((): SelectItem[] => [
	{
		title: i18n.t('components.config.journal.storages.volatile'),
		value: JournalPersistence.Volatile,
	},
	{
		title: i18n.t('components.config.journal.storages.persistent'),
		value: JournalPersistence.Persistent,
	},
]);
</script>
