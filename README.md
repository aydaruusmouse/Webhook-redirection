# Webhook-redirection

Twilio WhatsApp webhook bridge. Twilio cannot post directly to Chatwoot, so this app receives Twilio, returns TwiML immediately, and forwards the same payload to Chatwoot.

```
WhatsApp → Twilio → this app /api/twilio/incoming → Chatwoot
```

Chatwoot still sends agent replies back through Twilio.

## Twilio webhook URL

```
https://YOUR-DOMAIN/api/twilio/incoming
```

Method: **HTTP POST**

## Forward target

Default:

```
https://support.telesom.com/api/webhook/whatsapp
```

Override with `EXTERNAL_WEBHOOK_URL`.

## Setup

```bash
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
php artisan queue:work
```

Required `.env` values:

```
APP_URL=https://YOUR-DOMAIN
EXTERNAL_WEBHOOK_URL=https://support.telesom.com/api/webhook/whatsapp
TWILIO_ACCOUNT_SID=
TWILIO_AUTH_TOKEN=
TWILIO_PHONE_NUMBER=
```

Keep a queue worker running so Twilio is acknowledged fast and Chatwoot is called in the background.

## Production (Docker)

Point DNS at the server, then:

```bash
cp .env.example .env
# set APP_KEY, APP_URL, APP_DOMAIN
APP_DOMAIN=webhook.example.com docker compose up -d --build
```

Then set the Twilio number webhook to:

`https://webhook.example.com/api/twilio/incoming`
