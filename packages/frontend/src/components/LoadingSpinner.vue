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
	<div v-if='enabled' class='loading'>
		<div class='loading-group'>
			<div class='spinner' :style='{"--color": color}' />
			<div class='loading-text'>
				{{ text }}
			</div>
		</div>
	</div>
</template>

<script lang='ts'>
import {Component, Vue} from 'vue-property-decorator';
import {mapGetters} from 'vuex';
import ThemeManager from '@/helpers/themeManager';

@Component({
	computed: {
		...mapGetters({
			enabled: 'spinner/isEnabled',
			text: 'spinner/text',
		}),
	}
})

/**
 * Loading spinner component
 */
export default class LoadingSpinner extends Vue {

	/**
	 * Returns the spinner color
	 */
	get color(): string {
		return ThemeManager.getPrimaryColor();
	}

}
</script>

<style scoped lang='scss'>
.loading {
	position: fixed;
	width: 100%;
	height: 100%;
	background-color: rgb(0 0 0 / 75%);
	z-index: 9999;
	cursor: wait;
	overflow: auto;
	text-align: center;

	&::before {
		content: '';
		display: inline-block;
		height: 100%;
		vertical-align: middle;
	}
}

.loading-group {
	display: inline-block;
	vertical-align: middle;
}

.loading-text {
	margin-top: 20pt;
	font-size: 20pt;
	position: relative;
	z-index: 10000;
	color: white;
	text-align: center;
	white-space: pre-wrap;
}

.spinner {
	display: block;
	position: relative;
	z-index: 10000;
	width: 150px;
	height: 150px;
	margin: auto;
	border: 16px solid #f3f3f3;
	border-radius: 50%;
	border-top: 16px solid var(--color);
	animation: spin 2s linear infinite;
}

@keyframes spin {
	0% {
		transform: rotate(0deg);
	}

	100% {
		transform: rotate(360deg);
	}
}
</style>
