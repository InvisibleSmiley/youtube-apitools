# YouTube API Tools

## Processing Playlists

The implementation is currently limited to retrieving playlist items (video metadata) and playlist management.

See the [playground](playground) for examples.

## YouTube API access

Error 403, quotaExceeded may indicate that YouTube API access was revoked due to 90 days of inactivity.
In that case, don't bother trying to get access granted again. Instead, delete the project and create a new one.

Make sure to authorize (whitelist) the redirect URI(s), e.g. `http://localhost:8000/index.php`.

See https://console.developers.google.com/apis/credentials
