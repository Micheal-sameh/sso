# Donations: what's needed from NBE / CIB, and where it goes

The donation feature (see `app/Services/Payments/`) is fully built and
working today against a **sandbox gateway** (`MockDonationGateway`) so the
whole donate → pay → callback flow can be tested without a real bank
connection. `NbeDonationGateway` and `CibDonationGateway` are stubs that
throw until the two things below are done for each bank.

## 1. What to get from the bank

Each bank hands this over only after merchant/payment-gateway onboarding is
approved — it's not public documentation. Ask their merchant services team
for:

- [ ] **Integration guide / API reference** — the actual endpoints, request
      and response formats, and auth mechanism. This determines what goes
      inside `initiate()` in `NbeDonationGateway` / `CibDonationGateway`.
- [ ] **Merchant ID** (may be called Merchant Code / Terminal ID)
- [ ] **API key / secret** used to authenticate server-to-server requests
- [ ] **Webhook / callback signing secret** — used to verify that a payment
      notification genuinely came from the bank (HMAC or similar). Without
      this, `verifySignature()` can't be implemented safely.
- [ ] **Callback / webhook URL format they expect you to register**, and
      whether they support setting it per-merchant in a dashboard or need it
      sent to them to configure manually
- [ ] **Whether they support saved-card tokenization / recurring charges.**
      Many bank merchant APIs are one-shot hosted-payment-page only. If
      neither bank supports this, **monthly recurring donations can't run
      through them directly** — you'd need an aggregator (e.g. Paymob) for
      that piece specifically. Confirm this early since it affects the
      recurring donation feature already built.
- [ ] **Sandbox/test credentials**, if they offer a test environment,
      so the real integration can be verified before going live
- [ ] **Settlement details** — how/when paid-in donations actually reach
      the church's bank account, and any transaction fees, since that's
      relevant to reconciliation even though it doesn't affect the code

Also confirm apple pay support if you want it (see note at the bottom).

## 2. Where each piece goes

### Credentials → `.env` (never commit these)

```
NBE_SANDBOX=false
NBE_MERCHANT_ID=
NBE_API_KEY=
NBE_WEBHOOK_SECRET=

CIB_SANDBOX=false
CIB_MERCHANT_ID=
CIB_API_KEY=
CIB_WEBHOOK_SECRET=
```

These keys already exist in `config/services.php` under `nbe` / `cib` and
read from the env vars above. `NBE_SANDBOX` / `CIB_SANDBOX` default to
`true` — leave them `true` until that bank's gateway class (below) is
actually implemented, then flip to `false` to go live for that bank only.
The other bank can stay sandboxed independently.

### Integration logic → the gateway classes

| File | What to fill in |
|---|---|
| `app/Services/Payments/NbeDonationGateway.php` | `initiate()`, `verifySignature()`, `parseCallback()`, `chargeToken()` — replace the `throw new GatewayNotConfiguredException(...)` in each method with the real call, per NBE's integration guide |
| `app/Services/Payments/CibDonationGateway.php` | Same four methods, per CIB's integration guide |

Nothing else needs to change — `DonationGatewayFactory`, the controllers,
and the frontend already route through `DonationGatewayContract`, so once
these two classes are implemented and sandbox mode is off, real payments
flow through automatically.

### Webhook URL to register with the bank

```
https://your-domain.com/api/donations/callback/nbe
https://your-domain.com/api/donations/callback/cib
```

(`routes/api.php` — already wired to `DonationCallbackController`.)

### Callback return URL (where the bank redirects the donor's browser back)

```
https://your-domain.com/donations/return
```

## 3. Apple Pay

Apple Pay is a wallet UI layered on top of a payment processor, not a
processor itself. Whether donors can use it depends on whether NBE/CIB's
gateway offers it as a payment method inside their hosted page — ask about
this in the same onboarding conversation. If neither does, Apple Pay would
need a separate gateway (e.g. Paymob, which supports it) added alongside
NBE/CIB using the same `DonationGatewayContract` pattern. If a bank *does*
support it, you'll additionally need from Apple:

- [ ] An Apple **Merchant ID**
- [ ] A **Payment Processing Certificate**
- [ ] A **domain verification file**, served at
      `/.well-known/apple-developer-merchantid-domain-association`
      on your production domain
