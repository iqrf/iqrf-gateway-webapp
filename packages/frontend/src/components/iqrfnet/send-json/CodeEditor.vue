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
	<v-input>
		<v-field
			:active='isFocused || !!modelValue'
			:clearable='clearable'
			:label='label'
			class='i-code-editor__field'
			@click='onFieldClick'
		>
			<div class='i-code-editor__wrapper'>
				<PrismEditor
					ref='editor'
					v-model='modelValue'
					language='json'
					:line-numbers='isFocused || !!modelValue'
					:highlight='highlighter'
					@focusin='isFocused = true'
					@focusout='isFocused = false'
				/>
			</div>
			<template #append-inner>
				<div class='v-field__clearable'>
					<v-icon
						v-if='componentProps.clearable && modelValue'
						:icon='mdiCloseCircle'
						@click='modelValue = ""'
					/>
				</div>
			</template>
		</v-field>
	</v-input>
</template>

<script setup lang='ts'>
import { mdiCloseCircle } from '@mdi/js';
import prism from 'prismjs';
import { PropType, ref, type TemplateRef, useTemplateRef } from 'vue';
import { PrismEditor } from 'vue-prism-editor';

import 'vue-prism-editor/dist/prismeditor.min.css';
import 'prismjs/themes/prism.css';
import 'prismjs/components/prism-json';

const modelValue = defineModel({
	type: [String, undefined] as PropType<string|undefined>,
	required: false,
	default: null,
});
const componentProps = defineProps({
	clearable: {
		type: Boolean,
		required: false,
		default: false,
	},
	label: {
		type: [String, undefined] as PropType<string|undefined>,
		required: false,
		default: undefined,
	},
	language: {
		type: String,
		required: false,
		default: 'json',
		validator(value: unknown): boolean {
			if (prism.languages[value as string]) {
				return true;
			}
			console.warn(`Language '${value as string}' is not supported by PrismJS.`);
			return false;
		},
	},
});
defineSlots();

const editor: TemplateRef<InstanceType<typeof PrismEditor>> = useTemplateRef('editor');
const isFocused = ref(false);

function onFieldClick(): void {
	const root = editor.value?.$el;
	const textarea = root?.querySelector('.prism-editor__textarea') as HTMLTextAreaElement|null;
	if (textarea) {
		textarea.focus();
	}
}

function highlighter(code: string) {
	const grammar = prism.languages[componentProps.language];
	return prism.highlight(code, grammar, componentProps.language);
}
</script>

<style scoped>
.i-code-editor__wrapper {
	padding-top: 2.5rem;
	width: 100%;
}

.i-code-editor__field {
	width: 100%;
}
</style>

