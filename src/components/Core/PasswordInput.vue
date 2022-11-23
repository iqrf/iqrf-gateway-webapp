<template>
	<CInput
		v-model='password'
		v-bind='$attrs'
		:type='visible ? "text" : "password"'
		:label='label'
		:is-valid='isValid'
		:invalid-feedback='invalidFeedback'
		v-on='$listeners'
	>
		<template #prepend-content>
			<slot name='prepend-content' />
		</template>
		<template #append-content>
			<slot name='append-content' />
			<span @click='visible = !visible'>
				<FontAwesomeIcon
					:icon='(visible ? ["far", "eye-slash"] : ["far", "eye"])'
				/>
			</span>
		</template>
	</CInput>
</template>

<script lang='ts'>
import {Component, Prop, VModel, Vue} from 'vue-property-decorator';
import {CInput} from '@coreui/vue/src';
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';

/**
 * Password Input
 */
@Component({
	components: {
		CInput,
		FontAwesomeIcon,
	},
})
export default class PasswordInput extends Vue {

	/**
	 * @property {string} password Value
	 */
	@VModel({required: true, type: String}) password!: string;

	/**
	 * @property {string} label Input label
	 */
	@Prop({required: true}) label!: string;

	/**
	 * @property {boolean|null} isValid Is input valid?
   */
	@Prop({required: false}) isValid!: boolean|null;

	/**
	 * @property {string|null} invalidFeedback Error message
   */
	@Prop({required: false}) invalidFeedback!: string|null;

	/**
	 * @property {boolean} visible Password visible flag
   */
	private visible = false;

}
</script>
