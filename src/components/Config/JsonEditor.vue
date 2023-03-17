<!--
Copyright 2017-2023 IQRF Tech s.r.o.
Copyright 2019-2023 MICRORISC s.r.o.

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
	<v-input
		:class='inputClasses'
		v-bind='{errorMessages, focused, isValid, success, successMessages}'
	>
		<v-label
			absolute
			v-bind='{focused}'
			:color='validationStatus'
			:value='hasValue'
		>
			{{ label }}
		</v-label>
		<prism-editor
			v-bind='$attrs'
			v-model='json'
			:class='[]'
			:readonly='readonly'
			:highlight='highlighter'
			@input='onInput($event)'
			@change='onChange($event)'
			@focus='onFocus($event)'
			@blur='onBlur($event)'
		/>
	</v-input>
</template>

<script lang='ts'>
import {Component, Prop, Ref, Vue, Watch} from 'vue-property-decorator';
import {PrismEditor} from 'vue-prism-editor';
import 'vue-prism-editor/dist/prismeditor.min.css';
import Prism from 'prismjs/components/prism-core';
import 'prismjs/components/prism-json';
import 'prismjs/themes/prism.css';

/**
 * JSON editor with syntax highlighting
 */
@Component({
	components: {
		PrismEditor,
	},
})
export default class JsonEditor extends Vue {

	/**
	 * @var {string|null} id Component ID
	 */
	@Prop({default: null}) id?: string|null;

	/**
	 * @var {string|null} label Component label
	 */
	@Prop({default: null}) label?: string|null;

	/**
	 * @var {boolean} readonly Is read-only?
	 */
	@Prop({default: false}) readonly;

	/**
	 * @var {string|null} value JSON to edit/view
	 */
	@Prop({default: null}) value?: string|null;

	/**
	 * @var {string|null} successMessages Valid JSON feedback
	 */
	@Prop({default: null}) successMessages?: Array<string>|null;

	/**
	 * @var {string|null} invalidFeedback Invalid JSON feedback
	 */
	@Prop({default: null}) errorMessages?: Array<string>|null;

	/**
	 * @var {boolean} wasValidated Was JSON validated?
	 */
	@Prop({default: false}) isValid = false;

	/**
	 * @var {boolean|null} success Is JSON valid?
	 */
	@Prop({default: null}) success?: boolean|null;

	/**
	 * @var {Vue} input Prism editor component
	 */
	@Ref('input') readonly input!: Vue;

	/**
	 * @var {boolean} focused Is the input focused?
	 */
	private focused = false;

	/**
	 * @var {string|null} json JSON to render
	 */
	private json: string|null = null;

	/**
	 * JSON highlighter method
	 * @param {string} code text to highlight
	 */
	private highlighter(code: string): void {
		return Prism.highlight(code, Prism.languages.json, 'json');
	}

	get hasValue(): boolean {
		return this.value !== null && this.value !== '';
	}

	get validationStatus(): string {
		if (!this.focused) {
			return 'secondary';
		}
		if (this.success || this.successMessages !== null && this.successMessages?.length !== 0) {
			return 'success';
		}
		if (this.errorMessages !== null && this.errorMessages?.length !== 0) {
			return 'error';
		}
		return 'primary';
	}

	get inputClasses(): string {
		const classes = ['v-textarea', ' v-text-field', 'v-text-field--is-booted'];
		return classes.join(' ');
	}

	/**
	 * Vue lifecycle hook mounted
	 */
	mounted(): void {
		this.json = this.value ?? null;
	}

	/**
	 * Updates the rendered JSON
	 * @param {string} json New JSON to render
	 */
	@Watch('value')
	private onValueChanged(json: string): void {
		this.json = json;
	}

	/**
	 * Handles the input event
	 * @param {string} json JSON from the input
	 */
	onInput(json: string): void {
		this.$emit('input', json);
		this.$emit('update:value', json);
	}

	/**
	 * Handles the input change event
	 * @param {string} json JSON from the input
	 */
	onChange(json: string): void {
		this.$emit('change', json);
		this.$emit('update:value', json);
	}

	/**
	 * Handles the input blur event
	 * @param {Event} event Blur event
	 */
	onBlur(event: Event): void {
		this.$emit('blur', event);
	}

	/**
	 * Handles the input focus event
	 * @param {Event} event Focus event
	 */
	onFocus(event: Event): void {
		this.focused = true;
		this.$emit('focus', event);
	}

}
</script>
