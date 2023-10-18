/**
 * Copyright 2017-2023 IQRF Tech s.r.o.
 * Copyright 2019-2023 MICRORISC s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Component state
 */
export enum ComponentState {
	/// Component is created
	Created = 'created',
	/// Data required for component are being loaded
	Loading = 'loading',
	/// Data required for component are loaded
	Ready = 'ready',
	/// An error occurred
	Error = 'error',
	/// An error occurred during data loading
	FetchFailed = 'fetchFailed',
	/// Data not found
	NotFound = 'notFound',
}
