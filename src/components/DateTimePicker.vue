<template>
	<v-form>
		<v-row>
			<v-col cols='12' md='6'>
				<v-menu
					v-model='dateMenu'
					:close-on-content-click='false'
					:nudge-right='40'
					transition='scale-transition'
					offset-y
					min-width='auto'
				>
					<template #activator='{on, attrs}'>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: $t("forms.errors.date"),
							}'
						>
							<v-text-field
								v-model='_date'
								:label='$t("forms.fields.date")'
								prepend-icon='mdi-calendar'
								readonly
								:disabled='disabled'
								:success='touched ? valid : null'
								:error-messages='errors'
								v-bind='attrs'
								v-on='on'
							/>
						</ValidationProvider>
					</template>
					<v-date-picker
						v-model='_date'
						no-title
						:min='minDate'
						:max='maxDate'
						@input='dateMenu = false'
					/>
				</v-menu>
			</v-col>
			<v-col cols='12' md='6'>
				<v-menu
					v-model='timeMenu'
					:close-on-content-click='false'
					:nudge-right='40'
					transition='scale-transition'
					offset-y
					min-width='auto'
				>
					<template #activator='{on, attrs}'>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: $t("forms.errors.time"),
							}'
						>
							<v-text-field
								v-model='_time'
								:label='$t("forms.fields.time")'
								prepend-icon='mdi-clock-outline'
								readonly
								:disabled='disabled'
								:success='touched ? valid : null'
								:error-messages='errors'
								v-bind='attrs'
								v-on='on'
							/>
						</ValidationProvider>
					</template>
					<v-time-picker
						v-model='_time'
						ampm-in-title
						header-color='white'
						@input='timeMenu = false'
					/>
				</v-menu>
			</v-col>
		</v-row>
	</v-form>
</template>

<script lang='ts'>
import {Component, Prop, PropSync, Vue} from 'vue-property-decorator';
import {extend, ValidationProvider} from 'vee-validate';

import {required} from 'vee-validate/dist/rules';


@Component({
	components: {
		ValidationProvider,
	}
})
export default class DateTimePicker extends Vue {
	/**
	 * @property {boolean} disabled Pickers disabled
	 */
	@Prop({type: Boolean, required: false, default: false}) disabled!: boolean;

	/**
	 * @property {string} minDate Specifies minimum allowed date
	 */
	@Prop({type: String, required: false, default: undefined}) minDate!: string|undefined;

	/**
	 * @property {string} minDate Specifies maximum allowed date
	 */
	@Prop({type: String, required: false, default: undefined}) maxDate!: string|undefined;

	/**
	 * @property {string} date Date string
	 */
	@PropSync('date', {type: String, default: ''}) _date!: string;

	/**
	 * @property {string} time Time string
	 */
	@PropSync('time', {type: String, default: ''}) _time!: string;

	/**
	 * @var {boolean} dateMenu Date picker visibility
	 */
	private dateMenu = false;

	/**
	 * @var {boolean} timeMenu Time picker visibility
	 */
	private timeMenu = false;

	/**
	 * Initalizes validation rules
	 */
	created(): void {
		extend('required', required);
	}
}
</script>
