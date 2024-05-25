<template>
	<div>
		<h1>{{ $t('config.bridge.title') }}</h1>
		<v-card>
			<v-card-text>
				<ValidationObserver v-slot='{ invalid }'>
					<v-form>
						<v-row>
							<v-col>
								<h4>{{ $t('config.bridge.influx') }}</h4>
								<ValidationProvider
									v-slot='{ errors, touched, valid }'
									rules='required'
									:custom-messages='{
										required: $t("config.bridge.errors.influx.host")
									}'
								>
									<v-text-field
										v-model='config.influx.host'
										:label='$t("forms.fields.host")'
										:success='touched ? valid : null'
										:error-messages='errors'
									/>
								</ValidationProvider>
								<ValidationProvider
									v-slot='{ errors, touched, valid }'
									rules='required|integer|between:1024,65535'
									:custom-messages='{
										required: $t("config.bridge.errors.influx.port"),
										integer: $t("config.bridge.errors.influx.portInvalid"),
										between: $t("config.bridge.errors.influx.portInvalid")
									}'
								>
									<v-text-field
										v-model.number='config.influx.port'
										type='number'
										:label='$t("forms.fields.port")'
										:success='touched ? valid : null'
										:error-messages='errors'
									/>
								</ValidationProvider>
								<ValidationProvider
									v-slot='{ errors, touched, valid }'
									rules='required'
									:custom-messages='{
										required: $t("config.bridge.errors.influx.org")
									}'
								>
									<v-text-field
										v-model='config.influx.org'
										:label='$t("config.bridge.form.org")'
										:success='touched ? valid : null'
										:error-messages='errors'
									/>
								</ValidationProvider>
								<ExtendedTextField
									v-model='config.influx.user'
									:label='$t("forms.fields.username")'
									:description='$t("config.bridge.notes.oldAuth")'
								/>
								<PasswordInput
									v-model='config.influx.password'
									:label='$t("forms.fields.password")'
								/>
								<PasswordInput
									v-model='config.influx.token'
									:label='$t("config.bridge.form.token")'
									persistent-hint
									:hint='$t("config.bridge.notes.newAuth")'
								/>
							</v-col>
							<v-col>
								<h4>{{ $t('config.bridge.mqtt') }}</h4>
								<ValidationProvider
									v-slot='{ errors, touched, valid }'
									rules='required'
									:custom-messages='{
										required: $t("config.bridge.errors.mqtt.host")
									}'
								>
									<v-text-field
										v-model='config.mqtt.host'
										:label='$t("forms.fields.host")'
										:success='touched ? valid : null'
										:error-messages='errors'
									/>
								</ValidationProvider>
								<ValidationProvider
									v-slot='{ errors, touched, valid }'
									rules='required|integer|between:1024,65535'
									:custom-messages='{
										required: $t("config.bridge.errors.mqtt.port"),
										integer: $t("config.bridge.errors.mqtt.portInvalid"),
										between: $t("config.bridge.errors.mqtt.portInvalid")
									}'
								>
									<v-text-field
										v-model.number='config.mqtt.port'
										type='number'
										:label='$t("forms.fields.port")'
										:success='touched ? valid : null'
										:error-messages='errors'
									/>
								</ValidationProvider>
								<ValidationProvider
									v-slot='{ errors, touched, valid }'
									rules='required'
									:custom-messages='{
										required: $t("config.bridge.errors.mqtt.client")
									}'
								>
									<v-text-field
										v-model='config.mqtt.client'
										:label='$t("forms.fields.clientId")'
										:success='touched ? valid : null'
										:error-messages='errors'
									/>
								</ValidationProvider>
								<ValidationProvider
									v-slot='{ errors, touched, valid }'
									:rules='{
										required: config.mqtt.password.length > 0,
									}'
									:custom-messages='{
										required: $t("config.bridge.errors.mqtt.credentials")
									}'
								>
									<v-text-field
										v-model='config.mqtt.user'
										:label='$t("forms.fields.username")'
										:success='touched ? valid : null'
										:error-messages='errors'
									/>
								</ValidationProvider>
								<ValidationProvider
									v-slot='{ errors, touched, valid }'
									:rules='{
										required: config.mqtt.user.length > 0,
									}'
									:custom-messages='{
										required: $t("config.bridge.errors.mqtt.credentials")
									}'
								>
									<PasswordInput
										v-model='config.mqtt.password'
										:label='$t("forms.fields.password")'
										:success='touched ? valid : null'
										:error-messages='errors'
									/>
								</ValidationProvider>
								<ValidationProvider
									v-slot='{ errors, touched, valid }'
									rules='duplicate'
									:custom-messages='{
										duplicate: $t("config.bridge.errors.mqtt.subscriptionDuplicate")
									}'
								>
									<v-text-field
										v-model='topic'
										label='Subscription'
										:success='touched ? valid : null'
										:error-messages='errors'
									>
										<template #append-outer>
											<v-btn
												color='success'
												small
												:disabled='topic.length === 0 || duplicateTopic(topic)'
												@click='addTopic'
											>
												{{ $t('forms.add') }}
											</v-btn>
										</template>
									</v-text-field>
								</ValidationProvider>
								<v-list dense>
									<v-list-item
										v-for='(topic, i) of config.mqtt.topics'
										:key='topic'
										class='px-0'
										dense
									>
										<v-list-item-content>{{ i + 1 }}. {{ topic }}</v-list-item-content>
										<v-list-item-action class='mx-0 my-0'>
											<v-btn
												icon
												@click='removeTopic(i)'
											>
												<v-icon
													color='error'
												>
													mdi-delete
												</v-icon>
											</v-btn>
										</v-list-item-action>
									</v-list-item>
								</v-list>
							</v-col>
						</v-row>
						<v-btn
							color='primary'
							:disabled='invalid'
							@click='saveConfig'
						>
							{{ $t('forms.save') }}
						</v-btn>
					</v-form>
				</ValidationObserver>
			</v-card-text>
		</v-card>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import ExtendedTextField from '@/components/ExtendedTextField.vue';
import PasswordInput from '@/components/Core/PasswordInput.vue';
import { extend, ValidationObserver, ValidationProvider } from 'vee-validate';

import { between, integer, required } from 'vee-validate/dist/rules';
import FeatureConfigService from '@/services/FeatureConfigService';

import { AxiosError, AxiosResponse } from 'axios';
import { BridgeConfiguration } from '@/interfaces/Config/Bridge';
import { NavigationGuardNext, Route } from 'vue-router';
import { extendedErrorToast } from '@/helpers/errorToast';

@Component({
	components: {
		ExtendedTextField,
		PasswordInput,
		ValidationObserver,
		ValidationProvider,
	},
	beforeRouteEnter(_to: Route, from: Route, next: NavigationGuardNext): void {
		next((vm: Vue) => {
			if (!vm.$store.getters['features/isEnabled']('iqrfGatewayInfluxdbBridge')) {
				vm.$toast.error(
					vm.$t('config.bridge.messages.disabled').toString()
				);
				vm.$router.push(from.path);
			}
		});
	},
	metaInfo: {
		title: 'config.bridge.description'
	},
})

/**
 * IQRF Gateway InfluxDB Bridge configuration component
 */
export default class BridgeConfig extends Vue {

	/**
	 * @constant {string} name Name of InfluxDB Bridge feature
	 */
	private readonly name = 'bridge';

	/**
	 * @var {BridgeConfiguration} config InfluxDB Bridge configuration
	 */
	private config: BridgeConfiguration = {
		influx: {
			host: 'localhost',
			port: 8086,
			org: '',
			user: '',
			password: '',
			token: '',
			buckets: {
				gateway: 'gateway',
				devices: 'devices',
				sensors: 'sensors'
			}
		},
		mqtt: {
			client: '',
			host: 'localhost',
			port: 1883,
			user: '',
			password: '',
			topics: []
		},
		logLevel: 'INFO'
	};

	/**
	 * @var {string} topic Topic model
	 */
	private topic = '';

	/**
	 * Initializes validation rules
	 */
	created(): void {
		extend('between', between);
		extend('integer', integer);
		extend('required', required);
		extend('duplicate', (topic: string) => {
			return !this.duplicateTopic(topic);
		});
	}

	/**
	 * Fetches bridge configuration on component mount
	 */
	mounted(): void {
		this.getConfig();
	}

	/**
	 * Retrieves bridge configuration
	 */
	private getConfig(): void {
		this.$store.commit('spinner/SHOW');
		FeatureConfigService.getConfig(this.name)
			.then((response: AxiosResponse) => {
				this.config = response.data;
				this.$store.commit('spinner/HIDE');
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'config.bridge.messages.fetch.failure');
			});
	}

	/**
	 * Saves bridge configuration
	 */
	private saveConfig(): void {
		this.$store.commit('spinner/SHOW');
		FeatureConfigService.saveConfig(this.name, this.config)
			.then(() => {
				this.$store.commit('spinner/HIDE');
				this.$toast.success(
					this.$t('config.bridge.messages.save.success').toString()
				);
			})
			.catch((error: AxiosError) => {
				extendedErrorToast(error, 'config.bridge.messages.save.failure');
			});
	}

	/**
	 * Adds a subscription topic
	 */
	private addTopic(): void {
		this.config.mqtt.topics.push(this.topic);
		this.topic = '';
	}

	/**
	 * Removes a subscription topic by index
	 * @param {number} index Topic index
	 */
	private removeTopic(index: number): void {
		this.config.mqtt.topics.splice(index, 1);
	}

	/**
	 * Checks if topic already exists
	 * @param {string} topic Topic
	 * @return {boolean} true if topic already exists, false otherwise
	 */
	private duplicateTopic(topic: string): boolean {
		return this.config.mqtt.topics.includes(topic);
	}
}

</script>
