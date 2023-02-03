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
	<CFormGroup
		v-bind='{validFeedback, invalidFeedback, wasValidated, class: ["form-group"]}'
	>
		<template #label>
			<slot name='label'>
				<label v-if='label' :for='safeId'>
					{{ label }}
				</label>
			</slot>
		</template>

		<template #input>
			<prism-editor
				v-bind='$attrs'
				:id='safeId'
				ref='input'
				v-model='json'
				:class='inputClasses'
				:readonly='readonly'
				:highlight='highlighter'
				@input='onInput($event)'
				@change='onChange($event)'
				@focus='onFocus($event)'
				@blur='onBlur($event)'
			/>
		</template>

		<template
			v-for='slot in $options.slots'
			#[slot]
		>
			<slot :name='slot' />
		</template>
	</CFormGroup>
</template>

<script lang='ts'>
import {Component, Prop, Ref, Vue, Watch} from 'vue-property-decorator';
import {CFormGroup} from '@coreui/vue';
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
		CFormGroup,
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
	 * @var {string|null} validFeedback Valid JSON feedback
	 */
	@Prop({default: null}) validFeedback?: string|null;

	/**
	 * @var {string|null} invalidFeedback Invalid JSON feedback
	 */
	@Prop({default: null}) invalidFeedback?: string|null;

	/**
	 * @var {boolean} wasValidated Was JSON validated?
	 */
	@Prop({default: false}) wasValidated = false;

	/**
	 * @var {boolean|null} isValid Is JSON valid?
	 */
	@Prop({default: null}) isValid?: boolean|null;

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
	 * Generates safe ID for the component
	 * @return {string} Safe ID
	 */
	get safeId(): string {
		if (this.id || this.$attrs.id) {
			return this.id || this.$attrs.id;
		}
		return 'uid-' + Math.random().toString(36).substring(2);
	}

	/**
	 * Returns CSS classes for the input
	 * @return {Array<string>} Array of CSS classes for the input
	 */
	get inputClasses(): Array<string> {
		const validationClass = typeof this.isValid === 'boolean' ? (this.isValid ? 'is-valid' : 'is-invalid') : '';
		const focusedClass = this.focused ? 'focused' : '';
		return ['form-control', validationClass, focusedClass];
	}

	/**
	 * JSON highlighter method
	 * @param {string} code text to highlight
	 */
	private highlighter(code: string): void {
		return Prism.highlight(code, Prism.languages.json, 'json');
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
		this.focused = false;
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

<style lang='scss'>
.focused {
	color: #768192;
	background-color: #fff;
	border-color: #8bb8df;
	outline: 0;
	box-shadow: 0 0 0 0.2rem rgb(51 122 183 / 25%);

	&.is-invalid {
		border-color: #e55353;
		box-shadow: 0 0 0 0.2rem rgb(229 83 83 / 25%);
	}

	&.is-valid {
		border-color: #2eb85c;
		box-shadow: 0 0 0 0.2rem rgb(46 184 92 / 25%);
	}
}
</style>
