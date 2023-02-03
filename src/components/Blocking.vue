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
	<div v-if='enabled' class='blocking'>
		<div class='blocking-text'>
			{{ text }}
		</div>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {mapGetters} from 'vuex';

@Component({
	computed: {
		...mapGetters({
			enabled: 'blocking/isEnabled',
			text: 'blocking/text'
		}),
	}
})

/**
 * Blocking component
 */
export default class Blocking extends Vue {}
</script>

<style scoped>
.blocking {
	position: fixed;
	display: flex;
	flex-direction: column;
	justify-content: center;
	align-items: center;
	z-index: 10000;
	width: 100%;
	height: 100%;
	background-color: rgb(0 0 0 / 75%);
}

.blocking-text {
	z-index: 10001;
	color: white;
	font-size: 20pt;
	text-align: center;
	overflow: auto;
	word-wrap: break-word;
	max-width: 50%;
}
</style>
