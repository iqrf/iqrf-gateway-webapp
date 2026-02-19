from typing import Any, Dict, Type

def assert_prop_type_value(data: Dict[str, Any], prop: str, expected_type: Type, value: object) -> None:
	"""Asserts that a dictionary poperty exists, is of specified type and contains specified value."""
	_assert_prop_exists(data, prop)
	_assert_prop_type(data, prop, expected_type)
	_assert_prop_value(data, prop, value)

def assert_prop_type(data: Dict[str, Any], prop: str, expected_type: Type) -> None:
	"""Asserts that a dictionary property exists, and is of specified type."""
	_assert_prop_exists(data, prop)
	_assert_prop_type(data, prop, expected_type)

def _assert_prop_exists(data: Dict[str, Any], prop: str) -> None:
	"""Asserts that dictionary property exists."""
	assert prop in data

def _assert_prop_type(data: Dict[str, Any], prop: str, expected_type: Type) -> None:
	"""Asserts that dictionary property is of specified type."""
	assert isinstance(data[prop], expected_type)

def _assert_prop_value(data: Dict[str, Any], prop: str, value: object) -> None:
	"""Asserts that dictionary property contains specified value."""
	assert data[prop] == value
