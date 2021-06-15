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
		v-bind='{validFeedback, invalidFeedback, wasValidated, class: computedClasses}'
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
				v-model='json'
				:class='inputClasses'
				:readonly='readonly'
				:highlight='highlighter'
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
import {Component, Prop, VModel, Vue} from 'vue-property-decorator';
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

	@Prop() id?: string|null = null;
	@Prop() label?: string|null = null;
	@Prop() readonly = false;
	@VModel({type: String}) json?: string|null = null;

	@Prop() validFeedback?: string|null;
	@Prop() invalidFeedback?: string|null;
	@Prop() wasValidated = true;
	@Prop() isValid?: boolean;

	get safeId(): string {
		if (this.id || this.$attrs.id) {
			return this.id || this.$attrs.id;
		}
		return 'uid-' + Math.random().toString(36).substr(2);
	}

	get computedClasses(): Array<string> {
		return ['form-group'];
	}

	get inputClasses(): Array<string> {
		const validationClass = typeof this.isValid === 'boolean' ? (this.isValid ? 'is-valid' : 'is-invalid') : '';
		return ['form-control', validationClass];
	}

	/**
	 * JSON highlighter method
	 * @param {string} code text to highlight
	 */
	private highlighter(code: string) {
		return Prism.highlight(code, Prism.languages.json, 'json');
	}

}
</script>

<style>
.prism-editor-wrapper {
		min-height: 5em;
}
</style>
