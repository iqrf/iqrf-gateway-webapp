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
	<Card header-color='gray'>
		<template #title>
			{{ $t(`common.labels.${type}`) }}
		</template>
		<template #titleActions>
			<v-btn
				color='primary'
				size='small'
			>
				{{ $t('common.buttons.clipboard') }}
			</v-btn>
		</template>
		<prism-editor
			v-model='json'
			:highlight='highlighter'
			:readonly='true'
		/>
	</Card>
</template>

<script lang='ts' setup>
import prism from 'prismjs';
import { computed, type PropType } from 'vue';
import { PrismEditor } from 'vue-prism-editor';

import 'vue-prism-editor/dist/prismeditor.min.css';
import 'prismjs/themes/prism.css';
import 'prismjs/components/prism-json';
import Card from '@/components/Card.vue';

const componentProps = defineProps({
	message: {
		type: [String, null] as PropType<string | null>,
		default: null,
		required: true,
	},
	type: {
		type: String,
		required: true,
	},
});

const json = computed(() => {
	return componentProps.message ?? undefined;
});

function highlighter(code: string) {
	return prism.highlight(code, prism.languages.json, 'json');
}
</script>
