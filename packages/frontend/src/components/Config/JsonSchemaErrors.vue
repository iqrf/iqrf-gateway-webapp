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
	<v-alert
		v-if='errors !== undefined && errors.length !== 0'
		type='error'
		text
	>
		{{ $t('iqrfnet.sendJson.messages.error.validatorErrors') }}<br>
		<ol class='validation-errors'>
			<li v-for='error in errors' :key='error'>
				{{ error }}
			</li>
		</ol>
	</v-alert>
</template>

<script lang='ts'>
import {Component, Prop, Vue} from 'vue-property-decorator';

/**
 * JSON schema violations viewer
 */
@Component({})
export default class JsonSchemaErrors extends Vue {

	/**
	 * @var {Array<string>} errors JSON schema validation errors
	 */
	@Prop({required: true}) errors!: Array<string>;

}
</script>

<style scoped>
.validation-errors {
	white-space: pre-wrap;
	margin-bottom: 0;
	padding-left: 2em;
}
</style>
