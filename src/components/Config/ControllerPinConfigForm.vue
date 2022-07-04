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
	<ValidationObserver v-slot='{invalid}'>
		<v-dialog
			v-model='show'
			width='50%'
			persistent
		>
			<v-card>
				<v-card-title>{{ modalTitle }}</v-card-title>
				<v-card-text>
					<form>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: $t("config.controller.pins.errors.name")
							}'
						>
							<v-text-field
								v-model='profile.name'
								:label='$t("config.controller.pins.form.name")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='integer|required'
							:custom-messages='{
								integer: $t("config.controller.pins.errors.greenLed"),
								required: $t("config.controller.pins.errors.greenLed"),
							}'
						>
							<v-text-field
								v-model.number='profile.greenLed'
								type='number'
								:label='$t("config.controller.pins.form.greenLed")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='integer|required'
							:custom-messages='{
								integer: $t("config.controller.pins.errors.redLed"),
								required: $t("config.controller.pins.errors.redLed"),
							}'
						>
							<v-text-field
								v-model.number='profile.redLed'
								type='number'
								:label='$t("config.controller.pins.form.redLed")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='integer|required'
							:custom-messages='{
								integer: $t("config.controller.pins.errors.button"),
								required: $t("config.controller.pins.errors.button"),
							}'
						>
							<v-text-field
								v-model.number='profile.button'
								type='number'
								:label='$t("config.controller.pins.form.button")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<v-checkbox
							v-model='useI2c'
							:label='$t("config.controller.pins.form.useI2c")'
						/>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							:rules='{
								integer: useI2c,
								required: useI2c,
							}'
							:custom-messages='{
								integer: $t("config.controller.pins.errors.sck"),
								required: $t("config.controller.pins.errors.sck"),
							}'
						>
							<v-text-field
								v-model.number='profile.sck'
								type='number'
								:label='$t("config.controller.pins.form.sck")'
								:success='touched ? valid : null'
								:error-messages='errors'
								:disabled='!useI2c'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							:rules='{
								integer: useI2c,
								required: useI2c,
							}'
							:custom-messages='{
								integer: $t("config.controller.pins.errors.sda"),
								required: $t("config.controller.pins.errors.sda"),
							}'
						>
							<v-text-field
								v-model.number='profile.sda'
								type='number'
								:label='$t("config.controller.pins.form.sda")'
								:success='touched ? valid : null'
								:error-messages='errors'
								:disabled='!useI2c'
							/>
						</ValidationProvider>
					</form>
				</v-card-text>
				<v-card-actions>
					<v-btn
						:color='modalColor'
						:disabled='invalid'
						@click='saveProfile'
					>
						{{ $t('forms.save') }}
					</v-btn>
					<v-spacer />
					<v-btn
						color='secondary'
						@click='deactivateModal'
					>
						{{ $t('forms.cancel') }}
					</v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog>
	</ValidationObserver>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {integer, required} from 'vee-validate/dist/rules';

import {IControllerPinConfig} from '@/interfaces/controller';

@Component({
	components: {
		ValidationObserver,
		ValidationProvider,
	},
})

/**
 * Controller pin configuration form modal window component
 */
export default class ControllerPinConfigForm extends Vue {
	/**
	 * @var {boolean} show Controls whether modal window is rendered
	 */
	private show = false;

	/**
	 * @var {number} id Controller pin configuration ID
	 */
	private id = -1;

	/**
	 * @var {boolean} useI2c Use I2C pin inputs
	 */
	private useI2c = false;

	/**
	 * @var {IControllerPinConfig} defaultProfile Default Controller pin configuration profile
	 */
	private defaultProfile: IControllerPinConfig = {
		name: '',
		greenLed: 0,
		redLed: 0,
		button: 0,
		sck: 0,
		sda: 0,
	};

	/**
	 * @var {IControllerPinConfig} profile Controller pin configuration profile
	 */
	private profile: IControllerPinConfig = this.defaultProfile;

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('integer', integer);
		extend('required', required);
	}

	/**
	 * Computes modal color based on ID
	 * @return {string} Modal window color
	 */
	get modalColor(): string {
		return (this.id === -1 ? 'success' : 'primary');
	}

	/**
	 * Computes modal title based on ID
	 * @return {string} Modal window title
	 */
	get modalTitle(): string {
		if (this.id === -1) {
			return this.$t('config.controller.pins.add').toString();
		} else {
			return this.$t('config.controller.pins.edit').toString();
		}
	}

	/**
	 * Stores controller pin configuration profile and renders the modal window
	 * @param {IControllerPinConfig|null} profile Controller pin configuration profile
	 */
	public activateModal(profile: IControllerPinConfig|null = null): void {
		if (profile !== null) {
			this.profile = profile;
			this.id = (profile.id as number);
			if (profile.sck !== undefined && profile.sda !== undefined) {
				this.useI2c = true;
			}
		}
		this.show = true;
	}

	/**
	 * Emits controller pin configuration profile and closes the modal window
	 */
	private saveProfile(): void {
		const profile = JSON.parse(JSON.stringify(this.profile));
		if (!this.useI2c) {
			delete profile.sck;
			delete profile.sda;
		}
		this.$emit('save-profile', profile);
		this.deactivateModal();
	}

	/**
	 * Clears controller pin configuration profile and closes the modal window
	 */
	private deactivateModal(): void {
		this.show = false;
		this.useI2c = false;
		this.id = -1;
		this.profile = this.defaultProfile;
	}
}
</script>
