# Rechtliches (DSGVO) & Discord Developer Portal

## Erledigt im Projekt

- **Datenschutzerklärung** (Privacy Policy): `/privacy` – DSGVO-konform, erfasst OAuth-Daten, Session, Cookies, Dashboard- und Bot-Daten.
- **Impressum**: `/imprint` – Platzhalter **müssen Sie durch Ihre Angaben ersetzen** (Name, Anschrift, Kontakt, Verantwortlich für Inhalte).
- **Cookie-Hinweis**: `/cookies` – beschreibt die technisch notwendigen Cookies (Session, XSRF).
- **Nutzungsbedingungen** (Terms of Service): `/terms` – Nutzung von Bot und Dashboard, Haftung, Discord ToS.
- **Footer** mit Links zu allen vier Seiten ist auf der Startseite, im Dashboard und auf den rechtlichen Seiten eingebunden.

## Was Sie tun müssen

1. **Impressum ausfüllen**  
   In `resources/js/i18n/locales/de.json` (und ggf. `en.json`, `tr.json`) unter `legal.imprintContent` die Platzhalter `[Name und Anschrift...]` etc. durch Ihre echten Angaben ersetzen. Alternativ können Sie die Legal-Seiten durch eigene Texte (z. B. aus einem Generator) ersetzen.

2. **APP_URL setzen**  
   In `.env` muss `APP_URL` auf Ihre produktive Domain zeigen (z. B. `https://xdashboard.example.com`), damit die folgenden URLs für Discord korrekt sind.

## Discord Developer Portal – einzutragende URLs

Im [Discord Developer Portal](https://discord.com/developers/applications) unter Ihrer Application → **General Information** bzw. **OAuth2** eintragen:

| Feld | URL (ersetzen Sie `IHRE-DOMAIN` durch Ihre echte Domain) |
|------|--------------------------------------------------------|
| **Privacy Policy URL** | `https://IHRE-DOMAIN/privacy` |
| **Terms of Service URL** | `https://IHRE-DOMAIN/terms` |
| **Linked Roles Verification URL** | `https://IHRE-DOMAIN/auth/discord/verify` |

- **Privacy Policy URL** und **Terms of Service URL** sind für viele Discord-Features (z. B. Bot-Einladung, OAuth) vorgeschrieben.
- **Linked Roles Verification URL** wird für Discord Linked Roles genutzt; die Route leitet aktuell auf den normalen Discord-Login weiter.

Beispiel bei Domain `https://bot.example.com`:

- Privacy Policy URL: `https://bot.example.com/privacy`
- Terms of Service URL: `https://bot.example.com/terms`
- Linked Roles Verification URL: `https://bot.example.com/auth/discord/verify`
