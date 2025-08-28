# Aakash SMS API Documentation

Based on web research, here's the complete documentation for Aakash SMS API integration with the provided token: `e03c6792746ba77e2c26e4245a5c78e813878f0be3976f26d79072407ad5bc96`

## 🔗 API Endpoints Overview

**Base URL**: `https://sms.aakashsms.com/sms/`

### Available APIs:
1. **Send SMS** - `/v3/send` (Latest version)
2. **SMS Reports** - `/v1/report/api`  
3. **Credit Balance** - `/v1/credit`

## 📱 1. Send SMS API

**Endpoint**: `POST https://sms.aakashsms.com/sms/v3/send`

### Request Parameters:
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `auth_token` | string | ✅ | Your API token |
| `to` | string | ✅ | Comma separated 10-digit mobile numbers |
| `text` | string | ✅ | SMS message content |

### Example Request:
```php
$data = [
    'auth_token' => 'e03c6792746ba77e2c26e4245a5c78e813878f0be3976f26d79072407ad5bc96',
    'to' => '9801234567,9807654321',
    'text' => 'Your OTP is 123456'
];
```

### Success Response:
```json
{
    "error": false,
    "message": "1 messages has been queued for delivery.",
    "data": {
        "valid": [
            {
                "id": 2673160,
                "mobile": "977981*******",
                "text": "v3 sms test",
                "credit": 1,
                "network": "ncell",
                "status": "queued"
            }
        ],
        "invalid": [
            {
                "mobile": "988585584",
                "text": "v3 sms test",
                "credit": 0,
                "network": "N/A",
                "status": "aborted"
            }
        ]
    }
}
```

### Error Responses:
- **Invalid Token**: `{"error": true, "message": "The provided Auth Token is not valid.", "data": []}`
- **Missing Fields**: `{"error": true, "message": "The [field] field is required.", "data": []}`
- **Insufficient Balance**: `{"error": true, "message": "Not enough balance.", "data": []}`

## 📊 2. SMS Reports API

**Endpoint**: `POST https://sms.aakashsms.com/sms/v1/report/api`

### Request Parameters:
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `auth_token` | string | ✅ | Your API token |
| `page` | integer | ✅ | Page number (starts from 1) |

### Example Request:
```php
$data = [
    'auth_token' => 'e03c6792746ba77e2c26e4245a5c78e813878f0be3976f26d79072407ad5bc96',
    'page' => 1
];
```

### Response:
```json
{
    "error": false,
    "total_page": 1,
    "response_code": "201",
    "data": [
        {
            "id": 1968977,
            "receiver": "984*******",
            "network": "ntc",
            "message": "Test from aakashsms",
            "api_credit": "1",
            "delivery_at": "2019-05-30 16:35:01"
        }
    ]
}
```

## 💳 3. Credit Balance API

**Endpoint**: `POST https://sms.aakashsms.com/sms/v1/credit`

### Request Parameters:
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `auth_token` | string | ✅ | Your API token |

### Example Request:
```php
$data = [
    'auth_token' => 'e03c6792746ba77e2c26e4245a5c78e813878f0be3976f26d79072407ad5bc96'
];
```

### Response:
```json
{
    "available_credit": 1000,
    "total_sms_sent": 500,
    "response_code": 202
}
```