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
							:rules='{
								required: !disabled
							}'
							:custom-messages='{
								required: $t("forms.errors.date"),
							}'
						>
							<v-text-field
								v-model='date'
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
						v-model='date'
						no-title
						:min='minDate'
						:max='maxDate'
						@input='dateMenu = false; updateDate($event)'
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
							:rules='{
								required: !disabled
							}'
							:custom-messages='{
								required: $t("forms.errors.time"),
							}'
						>
							<v-text-field
								v-model='time'
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
						v-model='time'
						format='24hr'
						header-color='white'
						use-seconds
						@click:hour='_datetime.setHours($event)'
						@click:minute='_datetime.setMinutes($event);'
						@click:second='timeMenu = false;_datetime.setSeconds($event)'
					/>
				</v-menu>
			</v-col>
		</v-row>
	</v-form>
</template>

<script lang='ts'>
import {Component, Prop, PropSync, Vue, Watch} from 'vue-property-decorator';
import {extend, ValidationProvider} from 'vee-validate';

import {required} from 'vee-validate/dist/rules';
import {DateTime} from 'luxon';

@Component({
	components: {
		ValidationProvider,
	},
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
	 * @property {string} maxTime Specifies maximum allowed date
	 */
	@Prop({type: String, required: false, default: undefined}) maxDate!: string|undefined;

	/**
	 * @property {string} fromBrowser Allows to set date and time frow browser
	 */
	@Prop({type: Boolean, required: false, default: false}) fromBrowser!: boolean;

	/**
	 * @property {Date} datetime Date object
	 */
	@PropSync('datetime', {default: null, required: true}) _datetime!: Date|null;

	/**
	 * @var {string} date Date string
	 */
	private date = '';

	/**
	 * @var {string} time Time string
	 */
	private time = '';

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

	/**
	 * Datetime prop change watcher
	 */
	@Watch('_datetime')
	onDatetimeChanged(): void {
		if (this._datetime !== null) {
			const date = DateTime.fromJSDate(this._datetime);
			this.date = date.toISODate();
			this.time = date.toLocaleString(DateTime.TIME_24_WITH_SECONDS);
		}
	}

	/**
	 * Updates date object
	 * @param {string} date Date string
	 */
	private updateDate(date: string): void {
		const eventDate = new Date(date);
		if (this._datetime === null) {
			this._datetime = eventDate;
		} else {
			this._datetime.setFullYear(eventDate.getFullYear(), eventDate.getMonth(), eventDate.getDate());
		}
	}

	/**
	 * Sets date from Luxon DateTime object
	 * @param {DateTime} datetime Luxon DateTime object
	 */
	public setFromDateTime(datetime: DateTime): void {
		this._datetime = datetime.toJSDate();
		this.date = datetime.toISODate();
		this.time = datetime.toLocaleString(DateTime.TIME_24_WITH_SECONDS);
	}

}
</script>
