# PulseGuard API Documentation

Base URL: `/api` (e.g. `https://your-domain.com/api`)

## Authentication

API endpoints (except token creation) require a Bearer token.

### Create token

```http
POST /api/auth/token
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "your-password"
}
```

**Response (200):**

```json
{
  "token": "1|abc...",
  "type": "Bearer"
}
```

Use the token in subsequent requests:

```http
Authorization: Bearer 1|abc...
```

### Revoke token

```http
DELETE /api/auth/token
Authorization: Bearer 1|abc...
```

**Response:** 204 No Content

---

## Rate limiting

- **Auth:** 10 requests per minute per IP
- **API (authenticated):** 60 requests per minute per user

---

## Sites

### List sites

```http
GET /api/sites?per_page=15&active=1
Authorization: Bearer ...
```

| Query   | Type    | Description                    |
|---------|---------|--------------------------------|
| per_page| integer | 1–50, default 15               |
| active  | 0\|1    | Filter by is_active            |

**Response:** Paginated list of `MonitoredSite` resources.

---

### Create site

```http
POST /api/sites
Content-Type: application/json
Authorization: Bearer ...

{
  "name": "My Website",
  "url": "https://example.com",
  "check_interval_minutes": 1,
  "ssl_check_enabled": true,
  "alert_channels": ["slack", "mail"]
}
```

| Field                   | Type     | Required | Description                          |
|-------------------------|----------|----------|--------------------------------------|
| name                    | string   | yes      | Display name                         |
| url                     | string   | yes      | Full URL to monitor                  |
| check_interval_minutes  | integer  | no       | 1–60, default 1                      |
| ssl_check_enabled       | boolean  | no       | default true                         |
| alert_channels          | array    | no       | Values: slack, telegram, mail, webhook |

**Response (201):** Created `MonitoredSite` object.

---

### Get site

```http
GET /api/sites/{id}
Authorization: Bearer ...
```

---

### Update site

```http
PUT /api/sites/{id}
PATCH /api/sites/{id}
Content-Type: application/json
Authorization: Bearer ...

{
  "name": "New Name",
  "is_active": false
}
```

**Response:** Updated `MonitoredSite` object.

---

### Delete site

```http
DELETE /api/sites/{id}
Authorization: Bearer ...
```

**Response:** 204 No Content

---

### List HTTP checks for a site

```http
GET /api/sites/{id}/checks?limit=100
Authorization: Bearer ...
```

| Query  | Type    | Description        |
|--------|---------|--------------------|
| limit  | integer | 1–500, default 100 |

**Response:**

```json
{
  "data": [
    {
      "id": 1,
      "monitored_site_id": 1,
      "status_code": 200,
      "response_time_ms": 145,
      "status": "up",
      "error_message": null,
      "checked_at": "2024-01-15T10:00:00.000000Z"
    }
  ]
}
```

---

## Stats

### Site stats

```http
GET /api/sites/{id}/stats?days=30
Authorization: Bearer ...
```

| Query | Type    | Description     |
|-------|---------|-----------------|
| days  | integer | 1–90, default 30 |

**Response:**

```json
{
  "uptime_percentage": 99.95,
  "days": 30,
  "response_time": {
    "avg": 120,
    "min": 80,
    "max": 450,
    "points": [
      { "at": "2024-01-15T10:00:00.000000Z", "ms": 145 }
    ]
  },
  "current_status": "up",
  "last_checked_at": "2024-01-15T10:05:00.000000Z",
  "ssl_valid": true,
  "ssl_expires_at": "2025-02-01T00:00:00.000000Z",
  "open_incident": null
}
```

---

### Dashboard summary

```http
GET /api/dashboard
Authorization: Bearer ...
```

**Response:**

```json
{
  "sites": [
    {
      "id": 1,
      "name": "My Website",
      "url": "https://example.com",
      "uptime_percentage": 99.95,
      "status": "up",
      "last_checked_at": "2024-01-15T10:05:00.000000Z",
      "has_open_incident": false
    }
  ],
  "total_sites": 1
}
```

---

## Incidents

### List incidents for a site

```http
GET /api/sites/{id}/incidents?per_page=15
Authorization: Bearer ...
```

**Response:** Paginated list of `DowntimeIncident` resources.

---

## WebSocket events (optional)

If you use Laravel Reverb or Pusher, subscribe to:

- **Channel:** `pulseguard` (all events)
- **Channel:** `pulseguard.site.{siteId}` (per-site)

**Events:**

| Event           | Description        |
|-----------------|--------------------|
| `site.down`     | Site is down       |
| `site.recovered`| Site recovered     |
| `ssl.expiring`  | SSL expiring soon  |

Payloads include `site_id`, `site_name`, `site_url`, and event-specific fields.
