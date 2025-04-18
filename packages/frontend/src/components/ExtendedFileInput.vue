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
	<v-file-input
		v-model='model'
		:class='(inputInvalid && !showHint) || (showHint && !inputInvalid) ? "mb-4" : null'
		v-bind='$attrs'
		:label='label'
		:success='success'
		:error-count='errors.length'
		:error-messages='errors'
		:hint='description'
		:persistent-hint='showHint ?? false'
		v-on='$listeners'
	>
		<template v-for='(_, slotName) in $slots' #[slotName]>
			<slot :name='slotName' />
		</template>
		<template #message>
			<div
				v-for='(error,i) in errors'
				:key='i'
				class='extended-input-error'
			>
				{{ error }}
			</div>
			<div
				v-if='showHint && inputInvalid'
				class='extended-input-description mb-4'
			>
				{{ description }}
			</div>
		</template>
	</v-file-input>
</template>

<script lang='ts'>
import {Component, Prop, VModel, Vue} from 'vue-property-decorator';

@Component
export default class ExtendedFileInput extends Vue {
	/**
	 * @property {File|null} model File input model
	 */
	@VModel({required: true, default: null}) model!: File|null;

	/**
	 * @property {string} label File input label
	 */
	@Prop({required: false, default: ''}) label!: string;

	/**
	 * @property {string} description File input description/hint
	 */
	@Prop({required: false, default: ''}) description!: string;

	/**
	 * @property {boolean|null} success Input validation result
	 */
	@Prop({required: false}) success!: boolean|null;

	/**
	 * @property {Array<string>} errors Input validation error messages
	 */
	@Prop({required: false}) errors!: Array<string>|null;

	/**
	 * Computes whether hint should be shown
	 */
	get showHint(): boolean {
		return this.description.length > 0;
	}

	/**
	 * Computes whether input is invalid
	 */
	get inputInvalid(): boolean {
		return this.success !== null && !this.success;
	}
}
</script>
