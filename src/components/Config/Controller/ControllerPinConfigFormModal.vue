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
	<v-dialog
		v-model='showModal'
		width='50%'
		persistent
		no-click-animation
	>
		<ValidationObserver v-if='profile !== null' v-slot='{invalid}'>
			<v-card>
				<v-card-title>
					{{ modalTitle }}
				</v-card-title>
				<v-card-text>
					<v-form>
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
						<v-select
							v-model='profile.deviceType'
							:label='$t("config.controller.pins.form.deviceType")'
							:items='deviceTypeOptions'
						/>
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
							dense
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
					</v-form>
				</v-card-text>
				<v-card-actions>
					<v-spacer />
					<v-btn
						@click='hideModal'
					>
						{{ $t('forms.cancel') }}
					</v-btn>
					<v-btn
						:color='modalColor'
						:disabled='invalid'
						@click='saveProfile'
					>
						{{ $t('forms.save') }}
					</v-btn>
				</v-card-actions>
			</v-card>
		</ValidationObserver>
	</v-dialog>
</template>

<script lang='ts'>
import {Component, VModel, Vue} from 'vue-property-decorator';
import {extend, ValidationObserver, ValidationProvider} from 'vee-validate';

import {extendedErrorToast} from '@/helpers/errorToast';
import {integer, required} from 'vee-validate/dist/rules';

import {ConfigDeviceType} from '@/enums/Config/ConfigurationProfiles';
import ControllerPinConfigService from '@/services/ControllerPinConfigService';

import {AxiosError} from 'axios';
import {IControllerPinConfig} from '@/interfaces/Config/Controller';
import {ISelectItem} from '@/interfaces/Vuetify';

@Component({
	components: {
		ValidationObserver,
		ValidationProvider,
	},
})

/**
 * Controller pin configuration form modal window component
 */
export default class ControllerPinConfigFormModal extends Vue {

	/**
	 * @property {IControllerPinConfig|null} profile Edited profile
	 */
	@VModel({required: true, default: null}) profile!: IControllerPinConfig|null;

	/**
	 * @var {boolean} useI2c Use I2C pins
	 */
	private useI2c = false;

	/**
	 * Computes modal display condition
	 */
	get showModal(): boolean {
		return this.profile !== null;
	}

	/**
	 * Computes device type options
	 * @return {Array<ISelectItem>} Device type options
	 */
	get deviceTypeOptions(): Array<ISelectItem> {
		const types: Array<ConfigDeviceType> = [ConfigDeviceType.ADAPTER, ConfigDeviceType.BOARD];
		return types.map((item: ConfigDeviceType): ISelectItem => ({
			text: this.$t(`config.controller.pins.form.deviceTypes.${item}`).toString(),
			value: item,
		}));
	}

	/**
	 * Computes modal color based on ID
	 * @return {string} Modal window color
	 */
	get modalColor(): string {
		if (this.profile?.id === undefined) {
			return 'success';
		}
		return 'primary';
	}

	/**
	 * Computes modal title based on ID
	 * @return {string} Modal window title
	 */
	get modalTitle(): string {
		if (this.profile?.id === undefined) {
			return this.$t('config.controller.pins.add').toString();
		}
		return this.$t('config.controller.pins.edit').toString();
	}

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('integer', integer);
		extend('required', required);
	}

	/**
	 * Saves configuration profile
	 */
	private saveProfile(): void {
		if (this.profile === null) {
			return;
		}
		const profile = {...this.profile};
		if (!this.useI2c) {
			delete profile.sck;
			delete profile.sda;
		}
		const id = profile.id;
		const name = profile.name;
		delete profile.id;
		this.$store.commit('spinner/SHOW');
		if (id === undefined) {
			ControllerPinConfigService.add(profile)
				.then(() => this.handleSuccess(name))
				.catch((error: AxiosError) => this.handleFailure(error, name));
		} else {
			ControllerPinConfigService.edit(id, profile)
				.then(() => this.handleSuccess(name))
				.catch((error: AxiosError) => this.handleFailure(error, name));
		}
	}

	/**
	 * Handles REST API success
	 * @param {string} name Profile name
	 */
	private handleSuccess(name: string): void {
		this.$store.commit('spinner/HIDE');
		this.$toast.success(
			this.$t('config.controller.pins.messages.saveSuccess', {profile: name}).toString()
		);
		this.hideModal();
		this.$emit('update-profiles');
	}

	/**
	 * Handles REST API error
	 * @param {AxiosError} err Error response
	 * @param {string} name Profile name
	 */
	private handleFailure(err: AxiosError, name: string): void {
		extendedErrorToast(err, 'config.controller.pins.messages.saveFailed', {profile: name});
	}

	/**
	 * Hides modal window
	 */
	private hideModal(): void {
		this.profile = null;
	}
}
</script>
