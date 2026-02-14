from typing import Any, Dict, Type

def assert_prop_type_value(data: Dict[str, Any], prop: str, expected_type: Type, value: object) -> None:
	_assert_prop_exists(data, prop)
	_assert_prop_type(data, prop, expected_type)
	_assert_prop_value(data, prop, value)

def assert_prop_type(data: Dict[str, Any], prop: str, expected_type: Type) -> None:
	_assert_prop_exists(data, prop)
	_assert_prop_type(data, prop, expected_type)

def _assert_prop_exists(data: Dict[str, Any], prop: str) -> None:
	assert prop in data

def _assert_prop_type(data: Dict[str, Any], prop: str, expected_type: Type) -> None:
	assert isinstance(data[prop], expected_type)

def _assert_prop_value(data: Dict[str, Any], prop: str, value: object) -> None:
	assert data[prop] == value
