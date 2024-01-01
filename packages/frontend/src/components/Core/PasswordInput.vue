<!--
Copyright 2017-2024 IQRF Tech s.r.o.
Copyright 2019-2024 MICRORISC s.r.o.

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
	<v-text-field
		:id='id'
		v-model='password'
		v-bind='$attrs'
		:type='visible ? "text" : "password"'
		:label='label'
		:success='success'
		:error-messages='errorMessages'
		v-on='$listeners'
	>
		<template #prepend>
			<slot name='prepend' />
		</template>
		<template #prepend-outer>
			<slot name='prepend-outer' />
		</template>
		<template #append>
			<slot name='append' />
			<span @click='visible = !visible'>
				<v-icon>{{ visible ? 'mdi-eye' : 'mdi-eye-off' }}</v-icon>
			</span>
		</template>
		<template #append-outer>
			<slot name='append-outer' />
		</template>
	</v-text-field>
</template>

<script lang='ts'>
import {Component, Prop, VModel, Vue} from 'vue-property-decorator';

/**
 * Password Input
 */
@Component({})
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
	 * @property {boolean|null} success Is input valid?
   */
	@Prop({required: false}) success!: boolean|null;

	/**
	 * @property {string|null} errorMessages Error message
   */
	@Prop({required: false}) errorMessages!: string|null;

	/**
	 * @property {string|null} id Input ID
	 */
	@Prop({required: false, default: null}) id!: string|null;

	/**
	 * @property {boolean} visible Password visible flag
   */
	private visible = false;

}
</script>
