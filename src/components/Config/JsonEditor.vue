<!--
Copyright 2017-2021 IQRF Tech s.r.o.
Copyright 2019-2021 MICRORISC s.r.o.

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

@Component({
	components: {
		CFormGroup,
		PrismEditor,
	},
})
export default class JsonEditor extends Vue {

	@Prop({default: null}) id?: string|null;
	@Prop({default: null}) label?: string|null;
	@Prop({default: false}) readonly;
	@Prop({default: null}) value?: string|null;

	@Prop({default: null}) validFeedback?: string|null;
	@Prop({default: null}) invalidFeedback?: string|null;
	@Prop({default: false}) wasValidated = false;
	@Prop({default: null}) isValid?: boolean|null;

	@Ref('input') readonly input!: Vue;

	private focused = false;
	private json: string|null = null;

	get safeId(): string {
		if (this.id || this.$attrs.id) {
			return this.id || this.$attrs.id;
		}
		return 'uid-' + Math.random().toString(36).substr(2);
	}

	get inputClasses(): Array<string> {
		const validationClass = typeof this.isValid === 'boolean' ? (this.isValid ? 'is-valid' : 'is-invalid') : '';
		const focusedClass = this.focused ? 'focused' : '';
		return ['form-control', validationClass, focusedClass];
	}

	/**
	 * JSON highlighter method
	 * @param {string} code text to highlight
	 */
	private highlighter(code: string) {
		return Prism.highlight(code, Prism.languages.json, 'json');
	}

	mounted() {
		this.json = this.value ?? null;
	}

	@Watch('value')
	private onValueChanged(json: string): void {
		this.json = json;
	}

	onInput (json: string): void {
		this.$emit('input', json);
		this.$emit('update:value', json);
	}

	onChange (json: string): void {
		this.$emit('change', json);
		this.$emit('update:value', json);
	}

	onBlur(event: Event): void {
		this.focused = false;
		this.$emit('blur', event);
	}

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
