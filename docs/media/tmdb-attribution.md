# TMDB attribution and configuration

TMDB is off by default. Enable only after reviewing current TMDB API terms and setting the server-only token, numeric TV series ID, exact image base URL, terms acknowledgement, and commercial-licence flag where applicable.

```dotenv
TMDB_API_READ_TOKEN=
TMDB_TV_SERIES_ID=
TMDB_IMAGE_BASE_URL=https://image.tmdb.org/t/p
TMDB_TERMS_ACCEPTED=false
TMDB_COMMERCIAL_LICENSED=false
```

The provider fetches metadata on the server, caches it for six hours, exposes responsive image URLs but never the token, and never downloads/proxies image bytes. Enabled UI displays: “Image metadata and delivery provided by TMDB.” and “This product uses the TMDB API but is not endorsed or certified by TMDB.”

Commercial mode disables TMDB unless `TMDB_COMMERCIAL_LICENSED=true`. Configuration is an operational gate, not a legal conclusion.
