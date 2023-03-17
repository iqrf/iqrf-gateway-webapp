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
		<ValidationObserver v-if='operator !== null' v-slot='{invalid}'>
			<v-card>
				<v-card-title>
					<h5>{{ modalTitle }}</h5>
				</v-card-title>
				<v-card-text>
					<v-form>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: $t("network.operators.errors.nameRequired"),
							}'
						>
							<v-text-field
								v-model='operator.name'
								:label='$t("network.operators.form.name")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							rules='required'
							:custom-messages='{
								required: $t("network.operators.errors.apnRequired"),
							}'
						>
							<v-text-field
								v-model='operator.apn'
								:label='$t("network.operators.form.apn")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							:rules='{
								required: operator.password.length > 0
							}'
							:custom-messages='{
								required: $t("forms.errors.credentials"),
							}'
						>
							<v-text-field
								v-model='operator.username'
								:label='$t("network.operators.form.username")'
								:success='touched ? valid : null'
								:error-messages='errors'
							/>
						</ValidationProvider>
						<ValidationProvider
							v-slot='{errors, touched, valid}'
							:rules='{
								required: operator.username.length > 0
							}'
							:custom-messages='{
								required: $t("forms.errors.credentials"),
							}'
						>
							<v-text-field
								v-model='operator.password'
								:label='$t("network.operators.form.password")'
								:success='touched ? valid : null'
								:error-messages='errors'
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
						@click='saveOperator'
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

import {digits, required} from 'vee-validate/dist/rules';
import {extendedErrorToast} from '@/helpers/errorToast';

import NetworkOperatorService from '@/services/NetworkOperatorService';

import {AxiosError} from 'axios';
import {IOperator} from '@/interfaces/Network/Mobile';

@Component({
	components: {
		ValidationObserver,
		ValidationProvider,
	},
})

/**
 * Network operator form component
 */
export default class NetworkOperatorForm extends Vue {

	@VModel({required: true}) operator!: IOperator|null;

	/**
	 * Computes modal visibility condition
	 */
	get showModal(): boolean {
		return this.operator !== null;
	}

	/**
	 * Returns modal color depending on operator id
	 */
	get modalColor(): string {
		if (this.operator?.id === undefined) {
			return 'success';
		}
		return 'primary';
	}

	/**
	 * Returns modal title depending on operator title
	 */
	get modalTitle(): string {
		if (this.operator?.id === undefined) {
			return this.$t('network.operators.form.add').toString();
		}
		return this.$t('network.operators.form.edit').toString();
	}

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('digits', digits);
		extend('required', required);
	}

	/**
	 * Saves network operator configuration
	 */
	private saveOperator(): void {
		if (this.operator?.id === undefined) {
			this.addOperator();
		} else {
			this.editOperator();
		}
	}

	/**
	 * Saves new network operator
	 */
	private addOperator(): void {
		if (this.operator === null) {
			return;
		}
		const operator = this.filterOperator(this.operator);
		this.$store.commit('spinner/SHOW');
		NetworkOperatorService.addOperator(operator)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t('network.operators.messages.addSuccess', {operator: operator.name}).toString()
				);
				this.hideModal();
				this.$emit('saved');
			})
			.catch((err: AxiosError) => extendedErrorToast(err, 'network.operators.messages.addFailed'));
	}

	/**
	 * Edits existing network operator
	 */
	private editOperator(): void {
		if (this.operator === null || this.operator.id === undefined) {
			return;
		}
		const id = this.operator.id;
		const operator = this.filterOperator(this.operator);
		this.$store.commit('spinner/SHOW');
		NetworkOperatorService.editOperator(id, operator)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				
				this.$toast.success(
					this.$t('network.operators.messages.editSuccess', {operator: operator.name}).toString()
				);
				this.hideModal();
				this.$emit('saved');
			})
			.catch((err: AxiosError) => extendedErrorToast(err, 'network.operators.messages.editFailed'));
	}

	/**
	 * Filters operator configuration
	 * @param {IOperator} operator Operator
	 * @return {IOperator} Filtered operator
	 */
	private filterOperator(operator: IOperator): IOperator {
		delete operator.id;
		if (operator.username?.length === 0) {
			delete operator.username;
		}
		if (operator.password?.length === 0) {
			delete operator.password;
		}
		return operator;
	}

	/**
	 * Hides modal window
	 */
	private hideModal(): void {
		this.operator = null;
	}
}
</script>
