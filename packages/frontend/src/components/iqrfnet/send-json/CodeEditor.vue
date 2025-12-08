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
	<v-input :disabled='disabled'>
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
import { Theme } from '@iqrf/iqrf-vue-ui';
import { mdiCloseCircle } from '@mdi/js';
import { storeToRefs } from 'pinia';
import prism from 'prismjs';
import prism_dark from 'prismjs/themes/prism-okaidia.css?inline';
import prism_light from 'prismjs/themes/prism.css?inline';
import {
	computed,
	ComputedRef,
	PropType,
	ref,
	type TemplateRef,
	useTemplateRef,
	watch,
} from 'vue';
import { PrismEditor } from 'vue-prism-editor';

import { useThemeStore } from '@/store/theme';

import 'vue-prism-editor/dist/prismeditor.min.css';
import 'prismjs/components/prism-json';

const modelValue = defineModel({
	type: [String, undefined] as PropType<string|undefined>,
	required: false,
	default: null,
});
const componentProps = defineProps({
	/// Whether the field is clearable
	clearable: {
		type: Boolean,
		required: false,
		default: false,
	},
	/// Whether the field is disabled
	disabled: {
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

/// Theme store instance
const themeStore = useThemeStore();
/// Reactive reference to the current application theme
const { getTheme: currentTheme } = storeToRefs(themeStore);
/// Reference to the PrismEditor component
const editor: TemplateRef<InstanceType<typeof PrismEditor>> = useTemplateRef('editor');
/// Computed PrismJS theme based on the current application theme
const editorTheme: ComputedRef<string> = computed((): string => {
	switch (currentTheme.value) {
		case Theme.Dark:
			return prism_dark;
		case Theme.Light:
			return prism_light;
		default:
			return prism_light;
	}
});
/// Focus state of the code editor
const isFocused = ref(false);

/**
 * Focuses the textarea inside the code editor when the field is clicked
 */
function onFieldClick(): void {
	const root = editor.value?.$el;
	const textarea = root?.querySelector('.prism-editor__textarea') as HTMLTextAreaElement|null;
	if (textarea) {
		textarea.focus();
	}
}

/**
 * Highlights the code syntax
 * @param {string} code Code to highlight
 * @return {string} Highlighted code
 */
function highlighter(code: string): string {
	const grammar = prism.languages[componentProps.language];
	return prism.highlight(code, grammar, componentProps.language);
}

/**
 * Loads PrismJS theme
 */
function loadPrismTheme() {
	for (const s of document.querySelectorAll('style[data-prism_theme]')) {
		s.remove();
	}
	const tag = document.createElement('style');
	tag.dataset.prism_theme = '';
	tag.innerHTML = editorTheme.value;
	document.head.append(tag);
}

loadPrismTheme();
watch(currentTheme, () => loadPrismTheme());
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

