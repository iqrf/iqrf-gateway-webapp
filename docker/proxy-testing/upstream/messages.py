from datetime import datetime, timedelta, timezone
import json

def auth_success() -> str:
	"""Returns a JSON string representation of upstream auth success message."""
	dt = datetime.now(timezone.utc) + timedelta(hours=1)
	msg = {
		'type': 'auth_success',
		'expiration': dt.isoformat(timespec='seconds'),
		'service': False,
	}
	return json.dumps(msg)
