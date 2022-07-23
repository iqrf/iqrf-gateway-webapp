<template>
	<ValidationObserver>
		<v-form>
			<v-menu
				v-model='dateMenu'
				:close-on-content-click='false'
				:nudge-right='40'
				transition='scale-transition'
				offset-y
				min-width='auto'
			>
				<template #activator='{on, attrs}'>
					<v-text-field
						v-model='date'
						label='Date'
						readonly
						v-bind='attrs'
						v-on='on'
					/>
				</template>
				<v-date-picker
					v-model='date'
					no-title
					@input='dateMenu = false'
				/>
			</v-menu>
			<v-menu
				v-model='timeMenu'
				:close-on-content-click='false'
				:nudge-right='40'
				transition='scale-transition'
				offset-y
				min-width='auto'
			>
				<template #activator='{on, attrs}'>
					<v-text-field
						v-model='time'
						label='Time'
						readonly
						v-bind='attrs'
						v-on='on'
					/>
				</template>
				<v-time-picker
					v-model='time'
					full-width
					@input='timeMenu = false'
				/>
			</v-menu>
		</v-form>
	</ValidationObserver>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {required} from 'vee-validate/dist/rules';


@Component({
	components: {
		ValidationObserver,
		ValidationProvider,
	}
})
export default class DateTimePicker extends Vue {
	/**
	 * @var {boolean} dateMenu Date picker visibility
	 */
	private dateMenu = false;

	/**
	 * @var {boolean} timeMenu Time picker visibility
	 */
	private timeMenu = false;

	/**
	 * @var {string} date Date string
	 */
	private date = '';

	/**
	 * @var {string} time Time string
	 */
	private time = '';

	created(): void {
		extend('required', required);
	}
}
</script>
