<template>
	<v-text-field
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
	</v-text-field>
</template>

<script lang='ts'>
import {Component, Prop, VModel, Vue} from 'vue-property-decorator';

import {PropType} from 'vue/types/v3-component-props';

@Component
export default class ExtendedTextField extends Vue {
	/**
	 * @property {PropType<any>} model Text field model
	 */
	@VModel({required: true, default: null}) model!: PropType<any>;

	/**
	 * @property {string} label Text field label
	 */
	@Prop({required: false, default: ''}) label!: string;

	/**
	 * @property {string} description Text field description/hint
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
