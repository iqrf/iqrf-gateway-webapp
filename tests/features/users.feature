# Copyright 2017-2021 IQRF Tech s.r.o.
# Copyright 2019-2021 MICRORISC s.r.o.
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#     http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.

Feature: User manager

  @api
  Scenario: List all users before adding
	Given I log in as "admin" with password "iqrf"
	And I am an authenticated user
	When I create HTTP "GET" request to "/users" without body
	Then HTTP status code is 200
	And HTTP response contains JSON array of objects:
	  | id | username | role  | language |
	  | 1  | admin    | power | en       |

  @api
  Scenario: List all users after adding
	Given I log in as "admin" with password "iqrf"
	And I am an authenticated user
	When I create HTTP "GET" request to "/users" without body
	Then HTTP status code is 200
	And HTTP response contains JSON array of objects:
	  | id | username | role   | language |
	  | 1  | admin    | power  | en       |
	  | 2  | user     | normal | en       |

  @api
  Scenario: Change username without collision
	Given I log in as "admin" with password "iqrf"
	And I am an authenticated user
	When I create HTTP "PUT" request to "/users/2" with JSON object body:
	  | username |
	  | iqrf     |
	Then HTTP status code is 200

  @api
  Scenario: Change username
	Given I log in as "admin" with password "iqrf"
	And I am an authenticated user
	When I create HTTP "PUT" request to "/users/2" with JSON object body:
	  | username |
	  | iqrf     |
	Then HTTP status code is 200

  @api
  Scenario: Change username with collision
	Given I log in as "admin" with password "iqrf"
	And I am an authenticated user
	When I create HTTP "PUT" request to "/users/2" with JSON object body:
	  | username |
	  | admin    |
	Then HTTP status code is 409

  @api
  Scenario: Change username with invalid language
	Given I log in as "admin" with password "iqrf"
	And I am an authenticated user
	When I create HTTP "PUT" request to "/users/2" with JSON object body:
	  | language |
	  | invalid  |
	Then HTTP status code is 400

  @api
  Scenario: Change username with invalid role
	Given I log in as "admin" with password "iqrf"
	And I am an authenticated user
	When I create HTTP "PUT" request to "/users/2" with JSON object body:
	  | role    |
	  | invalid |
	Then HTTP status code is 400

  @api
  Scenario: Change nonexistent user
	Given I log in as "admin" with password "iqrf"
	And I am an authenticated user
	When I create HTTP "PUT" request to "/users/3" with JSON object body:
	  | username |
	  | iqrf     |
	Then HTTP status code is 404
